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
use FriendsOfHyperf\Trigger\Consumer;
use FriendsOfHyperf\Trigger\Traits\Logger;
use FriendsOfHyperf\Trigger\TriggerManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Engine\Channel;
use Hyperf\Engine\Coroutine;
use MySQLReplication\Definitions\ConstEventsNames;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Support\call;

class TriggerSubscriber extends AbstractSubscriber
{
    use Logger;

    protected Concurrent $concurrent;

    protected ?LoggerInterface $logger = null;

    protected ?Channel $chan = null;

    protected int $channelSize = 65535;

    public function __construct(
        protected ContainerInterface $container,
        protected TriggerManager $triggerManager,
        protected Consumer $consumer
    ) {
        $this->concurrent = new Concurrent(
            (int) ($consumer->getOption('concurrent.limit') ?? 1)
        );
        if ($consumer->getOption('channel.size')) {
            $this->channelSize = (int) $consumer->getOption('channel.size');
        }
        $this->logger = $this->getLogger();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'update' => 'onUpdate', // ConstEventsNames::UPDATE->value
            'delete' => 'onDelete', // ConstEventsNames::DELETE->value
            'write' => 'onWrite', // ConstEventsNames::WRITE->value
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

        $this->chan = new Channel($this->channelSize);

        Coroutine::create(function () {
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
                            $this->error((string) $e);
                            break;
                        } finally {
                            $closure = null;
                        }
                    }
                }
            } catch (Throwable $e) {
                $this->error((string) $e);
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

        $this->loop();

        $database = match (true) {
            method_exists($event, 'getTableMap') => $event->getTableMap()->getDatabase(),
            property_exists($event, 'tableMap') => $event->tableMap->database, // @phpstan-ignore-line
            default => null,
        };

        $table = match (true) {
            method_exists($event, 'getTableMap') => $event->getTableMap()->getTable(),
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
                method_exists($event, 'getValues') => $event->getValues(),
                property_exists($event, 'values') => $event->values, // @phpstan-ignore-line
                default => [],
            };
            foreach ($values as $value) {
                $this->chan->push(function () use ($callable, $value, $eventType) {
                    [$class, $method] = $callable;

                    if (! $this->container->has($class)) {
                        $this->warning(sprintf('Entry "%s" cannot be resolved.', $class));
                        return;
                    }

                    $args = match ($eventType) {
                        'write' => [$value],
                        'update' => [$value['before'], $value['after']],
                        'delete' => [$value],
                        default => null,
                    };

                    if (! $args) {
                        return;
                    }

                    try {
                        call([$this->container->get($class), $method], $args);
                    } catch (Throwable $e) {
                        $this->warning((string) $e);
                    }
                });
            }
        }
    }
}
