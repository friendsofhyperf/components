<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Monitor;

use FriendsOfHyperf\Trigger\Consumer;
use FriendsOfHyperf\Trigger\Event\OnReplicationStop;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use MySQLReplication\BinLog\BinLogCurrent;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class HealthMonitor
{
    protected ?BinLogCurrent $binLogCurrent = null;

    protected Timer $timer;

    public function __construct(protected ContainerInterface $container, protected Consumer $consumer)
    {
        $this->timer = new Timer($consumer->logger);
    }

    public function process(): void
    {
        Coroutine::create(function () {
            CoordinatorManager::until($this->consumer->identifier)->yield();

            $monitorInterval = $this->consumer->config->get('health_monitor.interval', 10);
            $snapShortInterval = (int) $this->consumer->config->get('snapshot.interval', 10);
            $context = ['connection' => $this->consumer->connection];

            // Monitor binLogCurrent
            $this->timer->tick($monitorInterval, function () use ($context) {
                if ($this->consumer->isStopped()) {
                    $this->consumer->logger?->warning('[{connection}] Health monitor stopped.', $context);
                    return Timer::STOP;
                }

                if ($this->binLogCurrent instanceof BinLogCurrent) {
                    $this->consumer->logger?->debug(
                        '[{connection}] Health monitoring, binLogCurrent: [{binlog_current}]',
                        $context +
                        [
                            'binlog_current' => json_encode($this->binLogCurrent->jsonSerialize(), JSON_THROW_ON_ERROR),
                        ]
                    );
                }
            });

            // Health check and set snapshot
            $this->timer->tick($snapShortInterval, function () use ($context) {
                if ($this->consumer->isStopped()) {
                    $this->consumer->logger?->warning('[{connection}] Snapshot stopped.', $context);
                    return Timer::STOP;
                }

                if (! $this->binLogCurrent instanceof BinLogCurrent) {
                    return;
                }

                $binLogCurrentCache = $this->consumer->binLogCurrentSnapshot->get();

                if (
                    $binLogCurrentCache instanceof BinLogCurrent
                    && $binLogCurrentCache->getBinLogPosition() == $this->binLogCurrent->getBinLogPosition()
                ) {
                    if ($this->container->has(EventDispatcherInterface::class)) {
                        $this->container->get(EventDispatcherInterface::class)?->dispatch(new OnReplicationStop($this->consumer->connection, $this->binLogCurrent));
                    }
                }

                $this->consumer->binLogCurrentSnapshot->set($this->binLogCurrent);
            });
        });
    }

    public function setBinLogCurrent(BinLogCurrent $binLogCurrent): void
    {
        $this->binLogCurrent = $binLogCurrent;
    }
}
