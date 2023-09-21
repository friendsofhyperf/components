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
use FriendsOfHyperf\Trigger\Traits\Logger;
use Hyperf\Collection\Arr;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use MySQLReplication\Config\ConfigBuilder;
use MySQLReplication\MySQLReplicationFactory;

use function Hyperf\Coroutine\wait;
use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class Consumer
{
    use Logger;

    protected ?string $name = null;

    private ?HealthMonitor $healthMonitor = null;

    private ?ServerMutexInterface $serverMutex = null;

    private BinLogCurrentSnapshotInterface $binLogCurrentSnapshot;

    private bool $stopped = false;

    public function __construct(
        protected subscriberManager $subscriberManager,
        protected TriggerManager $triggerManager,
        protected string $connection = 'default',
        protected array $options = [],
        protected ?StdoutLoggerInterface $logger = null
    ) {
        if (isset($options['name'])) {
            $this->name = $options['name'];
        }

        $this->binLogCurrentSnapshot = make(BinLogCurrentSnapshotInterface::class, [
            'consumer' => $this,
        ]);

        if ($this->getOption('server_mutex.enable', true)) {
            $this->serverMutex = make(ServerMutexInterface::class, [
                'name' => 'trigger:mutex:' . $this->connection,
                'owner' => Util::getInternalIp(),
                'options' => $this->getOption('server_mutex', []) + ['connection' => $this->connection],
            ]);
        }

        if ($this->getOption('health_monitor.enable', true)) {
            $this->healthMonitor = make(HealthMonitor::class, ['consumer' => $this]);
        }
    }

    public function start(): void
    {
        $callback = function () {
            // Health monitor
            if ($this->healthMonitor) {
                $this->healthMonitor->process();
            }

            $replication = $this->makeReplication();

            // Replication start
            CoordinatorManager::until($this->getIdentifier())->resume();

            $this->debug('Consumer started.');

            // Worker exit
            Coroutine::create(function () {
                CoordinatorManager::until(Constants::WORKER_EXIT)->yield();

                $this->stop();

                $this->warning('Consumer stopped.');
            });

            while (1) {
                if ($this->isStopped()) {
                    break;
                }

                wait(fn () => $replication->consume(), (float) $this->getOption('consume_timeout', 600));
            }
        };

        if ($this->serverMutex) {
            $this->serverMutex->attempt($callback);
        } else {
            $callback();
        }
    }

    public function getBinLogCurrentSnapshot(): BinLogCurrentSnapshotInterface
    {
        return $this->binLogCurrentSnapshot;
    }

    public function getHealthMonitor(): ?HealthMonitor
    {
        return $this->healthMonitor;
    }

    public function getName(): string
    {
        return $this->name ?? 'trigger-' . $this->connection;
    }

    public function getOption(?string $key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->options;
        }

        return Arr::get($this->options, $key, $default);
    }

    public function getConnection(): string
    {
        return $this->connection;
    }

    public function getIdentifier(): string
    {
        return sprintf('%s_start', $this->connection);
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
        // Get options
        $config = (array) $this->options;
        // Get databases of replication
        $databasesOnly = array_replace(
            $config['databases_only'] ?? [],
            $this->triggerManager->getDatabases($connection)
        );
        // Get tables of replication
        $tablesOnly = array_replace(
            $config['tables_only'] ?? [],
            $this->triggerManager->getTables($connection)
        );

        /** @var ConfigBuilder */
        $configBuilder = tap(
            new ConfigBuilder(),
            fn (ConfigBuilder $builder) => $builder->withUser($config['user'] ?? 'root')
                ->withHost($config['host'] ?? '127.0.0.1')
                ->withPassword($config['password'] ?? 'root')
                ->withPort((int) $config['port'] ?? 3306)
                ->withSlaveId(random_int(100, 999))
                ->withHeartbeatPeriod((float) $config['heartbeat_period'] ?? 3)
                ->withDatabasesOnly($databasesOnly)
                ->withTablesOnly($tablesOnly)
        );

        if ($binLogCurrent = $this->getBinLogCurrentSnapshot()->get()) {
            $configBuilder->withBinLogFileName($binLogCurrent->getBinFileName());
            $configBuilder->withBinLogPosition((int) $binLogCurrent->getBinLogPosition());

            $this->debug('Continue with position', $binLogCurrent->jsonSerialize());
        }

        $eventDispatcher = make(EventDispatcher::class);

        return tap(
            make(MySQLReplicationFactory::class, [
                'config' => $configBuilder->build(),
                'eventDispatcher' => $eventDispatcher,
            ]),
            function ($factory) use ($connection) {
                /** @var MySQLReplicationFactory $factory */
                $subscribers = $this->subscriberManager->get($connection);
                $subscribers[] = TriggerSubscriber::class;
                $subscribers[] = SnapshotSubscriber::class;

                foreach ($subscribers as $subscriber) {
                    $factory->registerSubscriber(make($subscriber, ['consumer' => $this]));
                }
            }
        );
    }
}
