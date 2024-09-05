<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Subscriber;

use Closure;
use FriendsOfHyperf\Trigger\ConstEventsNames;
use FriendsOfHyperf\Trigger\Consumer;
use FriendsOfHyperf\Trigger\TriggerManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Support\call;

class TriggerSubscriber extends AbstractSubscriber
{
    protected Concurrent $concurrent;

    protected ?Channel $chan = null;

    protected int $channelSize = 65535;

    public function __construct(
        protected ContainerInterface $container,
        protected TriggerManager $triggerManager,
        protected Consumer $consumer,
        protected ?LoggerInterface $logger = null
    ) {
        $this->concurrent = new Concurrent(
            (int) ($consumer->getOption('concurrent.limit') ?? 1)
        );
        if ($consumer->getOption('channel.size')) {
            $this->channelSize = (int) $consumer->getOption('channel.size');
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConstEventsNames::UPDATE->value => 'onUpdate',
            ConstEventsNames::DELETE->value => 'onDelete',
            ConstEventsNames::WRITE->value => 'onWrite',
        ];
    }

    protected function close(): void
    {
        $this->chan?->close();
        $this->chan = null;
    }

    protected function loop(): void
    {
        if ($this->chan) {
            return;
        }

        $context = ['connection' => $this->consumer->getConnection()];
        $this->chan = new Channel($this->channelSize);

        Coroutine::create(function () use ($context) {
            try {
                while (true) {
                    while (true) {
                        /** @var Closure|null $closure */
                        $closure = $this->chan?->pop();

                        if ($closure === null) {
                            break 2;
                        }

                        try {
                            $this->concurrent->create($closure);
                        } catch (Throwable $e) {
                            $this->logger?->error('[{connection}] ' . (string) $e, $context);
                            break;
                        } finally {
                            $closure = null;
                        }
                    }
                }
            } catch (Throwable $e) {
                $this->logger?->error('[{connection}] ' . (string) $e, $context);
            } finally {
                $this->close();
            }
        });

        Coroutine::create(function () {
            if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                $this->close();
            }
        });
    }

    protected function allEvents(EventDTO $event): void
    {
        if (! $event instanceof RowsDTO) {
            return;
        }

        $context = ['connection' => $this->consumer->getConnection()];
        $this->loop();

        $database = match (true) {
            method_exists($event, 'getTableMap') => $event->getTableMap()->getDatabase(), // v7.x, @deprecated, will removed in v3.2
            property_exists($event, 'tableMap') => $event->tableMap->database, // @phpstan-ignore-line
            default => null,
        };

        $table = match (true) {
            method_exists($event, 'getTableMap') => $event->getTableMap()->getTable(), // v7.x, @deprecated, will removed in v3.2
            property_exists($event, 'tableMap') => $event->tableMap->table, // @phpstan-ignore-line
            default => null,
        };

        $key = join('.', [
            $this->consumer->getConnection(),
            $database,
            $table,
            $event->getType(),
        ]);

        $eventType = $event->getType();

        foreach ($this->triggerManager->get($key) as $callable) {
            $values = match (true) {
                method_exists($event, 'getValues') => $event->getValues(), // v7.x, @deprecated, will removed in v3.2
                property_exists($event, 'values') => $event->values, // @phpstan-ignore-line
                default => [],
            };
            foreach ($values as $value) {
                $this->chan->push(function () use ($callable, $value, $eventType, $context) {
                    [$class, $method] = $callable;

                    if (! $this->container->has($class)) {
                        $this->logger?->warning(sprintf('[{connection}] Entry "%s" cannot be resolved.', $class), $context);
                        return;
                    }

                    $args = match ($eventType) {
                        ConstEventsNames::WRITE->value => [$value],
                        ConstEventsNames::UPDATE->value => [$value['before'], $value['after']],
                        ConstEventsNames::DELETE->value => [$value],
                        default => null,
                    };

                    if (! $args) {
                        return;
                    }

                    try {
                        call([$this->container->get($class), $method], $args);
                    } catch (Throwable $e) {
                        $this->logger?->warning('[{connection}] ' . (string) $e, $context);
                    }
                });
            }
        }
    }
}
