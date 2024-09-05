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
use FriendsOfHyperf\Trigger\Snapshot\BinLogCurrentSnapshotInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Coroutine\Coroutine;
use MySQLReplication\BinLog\BinLogCurrent;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class HealthMonitor
{
    protected BinLogCurrent $binLogCurrent;

    protected int $monitorInterval = 10;

    protected int $snapShortInterval = 10;

    protected string $connection;

    protected BinLogCurrentSnapshotInterface $binLogCurrentSnapshot;

    protected Timer $timer;

    public function __construct(
        protected ContainerInterface $container,
        protected Consumer $consumer,
        protected ?LoggerInterface $logger = null
    ) {
        $this->connection = $consumer->getConnection();
        $this->monitorInterval = (int) $consumer->getOption('health_monitor.interval', 10);
        $this->snapShortInterval = (int) $consumer->getOption('snapshot.interval', 10);
        $this->binLogCurrentSnapshot = $consumer->getBinLogCurrentSnapshot();
        $this->timer = new Timer($logger);
    }

    public function process(): void
    {
        Coroutine::create(function () {
            CoordinatorManager::until($this->consumer->getIdentifier())->yield();

            // Monitor binLogCurrent
            $this->timer->tick($this->monitorInterval, function () {
                if ($this->binLogCurrent instanceof BinLogCurrent) {
                    $this->logger?->debug(
                        '[{connection}] Health monitoring, binLogCurrent: [{binlog_current}]',
                        [
                            'connection' => $this->connection,
                            'binlog_current' => json_encode($this->binLogCurrent->jsonSerialize(), JSON_THROW_ON_ERROR),
                        ]
                    );
                }
            });

            // Health check and set snapshot
            $this->timer->tick($this->snapShortInterval, function () {
                if (! $this->binLogCurrent instanceof BinLogCurrent) {
                    return;
                }

                $binLogCurrentCache = $this->binLogCurrentSnapshot->get();

                if (
                    $binLogCurrentCache instanceof BinLogCurrent
                    && $binLogCurrentCache->getBinLogPosition() == $this->binLogCurrent->getBinLogPosition()
                ) {
                    if ($this->container->has(EventDispatcherInterface::class)) {
                        $this->container->get(EventDispatcherInterface::class)?->dispatch(new OnReplicationStop($this->connection, $this->binLogCurrent));
                    }
                }

                $this->binLogCurrentSnapshot->set($this->binLogCurrent);
            });
        });
    }

    public function setBinLogCurrent(BinLogCurrent $binLogCurrent): void
    {
        $this->binLogCurrent = $binLogCurrent;
    }
}
