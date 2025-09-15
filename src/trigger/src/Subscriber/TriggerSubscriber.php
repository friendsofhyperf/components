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

use FriendsOfHyperf\Trigger\ConstEventsNames;
use FriendsOfHyperf\Trigger\Consumer;
use FriendsOfHyperf\Trigger\TriggerManager;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Concurrent;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Engine\Channel;
use MySQLReplication\Event\DTO\EventDTO;
use MySQLReplication\Event\DTO\RowsDTO;
use Psr\Container\ContainerInterface;
use Throwable;

use function Hyperf\Support\call;
use function Hyperf\Support\msleep;
use function Hyperf\Tappable\tap;

class TriggerSubscriber extends AbstractSubscriber
{
    protected ?Concurrent $concurrent = null;

    protected ?Channel $chan = null;

    protected int $channelSize = 65535;

    public function __construct(
        protected ContainerInterface $container,
        protected TriggerManager $triggerManager,
        protected Consumer $consumer
    ) {
        $concurrentLimit = (int) ($consumer->config->get('concurrent.limit') ?? 1);
        if ($concurrentLimit > 0) {
            $this->concurrent = new Concurrent($concurrentLimit);
        }
        if ($consumer->config->has('channel.size')) {
            $this->channelSize = (int) $consumer->config->get('channel.size');
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

    protected function allEvents(EventDTO $event): void
    {
        // Only process RowsDTO events.
        if (! $event instanceof RowsDTO) {
            return;
        }

        // Start loop if not started.
        $this->loop();

        // Process event.
        $this->process($event);
    }

    /**
     * Process event.
     */
    protected function process(RowsDTO $event): void
    {
        $database = match (true) {
            method_exists($event, 'getTableMap') => $event->getTableMap()->getDatabase(), // v7.x, @deprecated, will be removed in v3.2
            property_exists($event, 'tableMap') => $event->tableMap->database, // @phpstan-ignore property.private
            default => null,
        };

        $table = match (true) {
            method_exists($event, 'getTableMap') => $event->getTableMap()->getTable(), // v7.x, @deprecated, will be removed in v3.2
            property_exists($event, 'tableMap') => $event->tableMap->table, // @phpstan-ignore property.private
            default => null,
        };

        $key = join('.', [
            $this->consumer->connection,
            $database,
            $table,
            $event->getType(),
        ]);

        $eventType = $event->getType();
        $values = match (true) {
            method_exists($event, 'getValues') => $event->getValues(), // v7.x, @deprecated since v3.1, will be removed in v3.2
            property_exists($event, 'values') => $event->values, // @phpstan-ignore property.private
            default => [],
        };
        $arguments = [];
        foreach ($values as $value) {
            $arguments[] = match ($eventType) {
                ConstEventsNames::WRITE->value => [$value],
                ConstEventsNames::UPDATE->value => [$value['before'], $value['after']],
                ConstEventsNames::DELETE->value => [$value],
                default => [],
            };
        }

        if (! ($chan = $this->chan) || count($arguments) === 0) {
            return;
        }

        foreach ($this->triggerManager->get($key) as $callable) {
            [$class, $method] = $callable;

            if (
                ! $this->container->has($class)
                || ! method_exists($class, $method)
            ) {
                $this->consumer->logger?->warning("[{$this->consumer->connection}] Class {$class} or method {$method} not found in container", ['connection' => $this->consumer->connection]);
                continue;
            }

            foreach ($arguments as $args) {
                if (! $args) {
                    continue;
                }

                $chan->push([$class, $method, $args]);
            }
        }
    }

    /**
     * Start loop to process events.
     */
    protected function loop(): void
    {
        $this->chan ??= tap(new Channel($this->channelSize), function () {
            $context = ['connection' => $this->consumer->connection];

            // Start coroutine to consume events from channel.
            Coroutine::create(function () use ($context) {
                try {
                    while (true) {
                        while (true) {
                            /** @var array{0:class-string,1:string,2:array}|false|null $payload */
                            $payload = $this->chan?->pop();

                            if (! is_array($payload)) {
                                break 2;
                            }

                            try {
                                $container = $this->container;
                                $consumer = $this->consumer;
                                $callable = static function () use ($payload, $context, $container, $consumer) {
                                    try {
                                        [$class, $method, $args] = $payload;
                                        // Call the method with arguments.
                                        $container->get($class)->{$method}(...$args);
                                    } catch (Throwable $e) {
                                        $consumer->logger?->warning('[{connection}] ' . (string) $e, $context);
                                    } finally {
                                        $args = [];
                                    }
                                };

                                // Execute in concurrent.
                                if ($this->concurrent) {
                                    $this->concurrent->create($callable);
                                } else {
                                    Coroutine::create($callable);
                                }
                            } catch (Throwable $e) {
                                $this->consumer->logger?->error('[{connection}] ' . (string) $e, $context);
                                break;
                            } finally {
                                $payload = null;
                                $callable = null;
                            }
                        }
                    }
                } catch (Throwable $e) {
                    $this->consumer->logger?->error('[{connection}] ' . (string) $e, $context);
                } finally {
                    $this->close();
                }
            });

            // Start coroutine to listen for worker exit signal.
            Coroutine::create(function () {
                if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield()) {
                    while (! $this->chan?->isEmpty()) {
                        msleep(100);
                    }

                    $this->close();
                }
            });
        });
    }

    protected function close(): void
    {
        $chan = $this->chan;
        $chan?->close();

        // Based on local snapshots and identity checks to avoid race conditions.
        if ($this->chan === $chan) {
            $this->chan = null;
        }
    }
}
