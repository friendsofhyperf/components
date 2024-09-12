<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

use FriendsOfHyperf\Trigger\Monitor\HealthMonitor;
use FriendsOfHyperf\Trigger\Mutex\ServerMutexInterface;
use FriendsOfHyperf\Trigger\Snapshot\BinLogCurrentSnapshotInterface;
use FriendsOfHyperf\Trigger\Subscriber\SnapshotSubscriber;
use FriendsOfHyperf\Trigger\Subscriber\TriggerSubscriber;
use Hyperf\Config\Config;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Stringable\Str;
use MySQLReplication\Config\ConfigBuilder;
use MySQLReplication\MySQLReplicationFactory;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class Consumer
{
    public readonly Config $config;

    public readonly string $name;

    public readonly string $identifier;

    public readonly ?HealthMonitor $healthMonitor;

    public readonly ?ServerMutexInterface $serverMutex;

    public readonly BinLogCurrentSnapshotInterface $binLogCurrentSnapshot;

    private bool $stopped = false;

    public function __construct(
        protected readonly SubscriberManager $subscriberManager,
        protected readonly TriggerManager $triggerManager,
        public readonly string $connection = 'default',
        array $options = [],
        public readonly ?LoggerInterface $logger = null
    ) {
        $this->name = $options['name'] ?? sprintf('trigger.%s', $this->connection);
        $this->identifier = $options['identifier'] ?? sprintf('trigger.%s', $this->connection);
        $this->config = new Config($options);

        $this->binLogCurrentSnapshot = make(BinLogCurrentSnapshotInterface::class, ['consumer' => $this]);
        $this->healthMonitor = $this->config->get('health_monitor.enable', true) ? make(HealthMonitor::class, ['consumer' => $this]) : null;
        $this->serverMutex = $this->config->get('server_mutex.enable', true) ? make(ServerMutexInterface::class, [
            'name' => 'trigger:mutex:' . $this->connection,
            'owner' => Util::getInternalIp(),
            'options' => $this->config->get('server_mutex', []) + ['connection' => $this->connection],
            'logger' => $this->logger,
        ]) : null;
    }

    public function start(): void
    {
        $context = ['connection' => $this->connection];
        $callback = function () use ($context) {
            // Reset stopped status
            $this->stopped = false;

            // Health monitor start
            $this->healthMonitor?->process();

            $replication = $this->makeReplication();

            // Replication start
            CoordinatorManager::until($this->identifier)->resume();

            $this->logger?->debug('[{connection}] Consumer started.', $context);

            // Worker exit
            Coroutine::create(function () use ($context) {
                CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
                $this->stop();
                $this->logger?->warning('[{connection}] Consumer exit.', $context);
            });

            while (1) {
                if ($this->isStopped()) {
                    $this->logger?->warning('[{connection}] Consumer stopped.', $context);
                    break;
                }

                try {
                    $replication->consume();
                } catch (Throwable $e) {
                    $this->logger?->warning('[{connection}] Error occurred, will retry later.', $context + ['message' => $e->getMessage()]);
                    $this->logger?->error((string) $e);
                    $this->stop();
                }
            }
        };

        if ($this->serverMutex) {
            $this->serverMutex->attempt($callback);
        } else {
            $callback();
        }
    }

    /**
     * @deprecated use `$this->binLogCurrentSnapshot` instead, will remove in v3.2.
     */
    public function getBinLogCurrentSnapshot(): BinLogCurrentSnapshotInterface
    {
        return $this->binLogCurrentSnapshot;
    }

    /**
     * @deprecated use `$this->healthMonitor` instead, will remove in v3.2.
     */
    public function getHealthMonitor(): ?HealthMonitor
    {
        return $this->healthMonitor;
    }

    /**
     * @deprecated use `$this->name` instead, will remove in v3.2.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @deprecated use `$this->config->get($key, $default)` instead, will remove in v3.2.
     */
    public function getOption(?string $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return (fn () => $this->configs ?? [])->call($this->config);
        }

        return $this->config->get($key, $default);
    }

    /**
     * @deprecated use `$this->connection` instead, will remove in v3.2.
     */
    public function getConnection(): string
    {
        return $this->connection;
    }

    /**
     * @deprecated use `$this->identifier` instead, will remove in v3.2.
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function stop(): void
    {
        $this->stopped = true;
        $this->serverMutex?->release();
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    protected function makeReplication(): MySQLReplicationFactory
    {
        $connection = $this->connection;
        // Get databases of replication
        $databasesOnly = array_replace(
            $this->config->get('databases_only', []),
            $this->triggerManager->getDatabases($connection)
        );
        // Get tables of replication
        $tablesOnly = array_replace(
            $this->config->get('tables_only', []),
            $this->triggerManager->getTables($connection)
        );

        $configBuilder = (new ConfigBuilder())
            ->withUser($this->config->get('user', 'root'))
            ->withHost($this->config->get('host', '127.0.0.1'))
            ->withPassword($this->config->get('password', 'root'))
            ->withPort((int) $this->config->get('port', 3306))
            ->withSlaveId(random_int(10000, 9999999))
            ->withHeartbeatPeriod((float) $this->config->get('heartbeat_period', 3))
            ->withDatabasesOnly($databasesOnly)
            ->withTablesOnly($tablesOnly);

        if (method_exists($configBuilder, 'withSlaveUuid')) { // php-mysql-replication >= 8.0
            $configBuilder->withSlaveUuid(Str::uuid()->toString());
        }

        if ($binLogCurrent = $this->binLogCurrentSnapshot->get()) {
            $configBuilder->withBinLogFileName($binLogCurrent->getBinFileName())
                ->withBinLogPosition($binLogCurrent->getBinLogPosition());

            $this->logger?->debug(
                '[{connection}] Continue with position, binLogCurrent: {binlog_current}',
                compact('connection') + ['binlog_current' => json_encode($binLogCurrent->jsonSerialize())]
            );
        }

        $eventDispatcher = make(EventDispatcher::class);

        return tap(
            make(MySQLReplicationFactory::class, [
                'config' => $configBuilder->build(),
                'eventDispatcher' => $eventDispatcher,
                'logger' => $this->logger,
            ]),
            function (MySQLReplicationFactory $factory) use ($connection) {
                $subscribers = $this->subscriberManager->get($connection);
                $subscribers[] = TriggerSubscriber::class;
                $subscribers[] = SnapshotSubscriber::class;

                foreach ($subscribers as $subscriber) {
                    $factory->registerSubscriber(make($subscriber, [
                        'consumer' => $this,
                        'logger' => $this->logger,
                    ]));
                }
            }
        );
    }
}
