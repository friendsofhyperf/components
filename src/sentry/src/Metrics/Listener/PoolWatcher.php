<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics\Listener;

use FriendsOfHyperf\Sentry\Feature;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Pool\Pool;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;

use function FriendsOfHyperf\Sentry\metrics;
use function Hyperf\Coroutine\defer;

abstract class PoolWatcher implements ListenerInterface
{
    protected Timer $timer;

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature,
    ) {
        $this->timer = new Timer();
    }

    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * Get the metric name prefix for this pool type (e.g., 'redis', 'mysql').
     *
     * @return string The prefix used in metric names like '{prefix}_connections_in_use'
     */
    abstract public function getPrefix(): string;

    /**
     * @param object|BeforeWorkerStart|MainCoroutineServerStart $event
     */
    abstract public function process(object $event): void;

    public function watch(Pool $pool, string $poolName, int $workerId): void
    {
        if (! $this->feature->isMetricsEnabled()) {
            return;
        }

        $timerId = $this->timer->tick($this->feature->getMetricsInterval(), function () use (
            $pool,
            $workerId,
            $poolName
        ) {
            defer(fn () => metrics()->flush());

            metrics()->gauge(
                $this->getPrefix() . '_connections_in_use',
                (float) $pool->getCurrentConnections(),
                [
                    'pool' => $poolName,
                    'worker' => (string) $workerId,
                ]
            );
            metrics()->gauge(
                $this->getPrefix() . '_connections_in_waiting',
                (float) $pool->getConnectionsInChannel(),
                [
                    'pool' => $poolName,
                    'worker' => (string) $workerId,
                ]
            );
            metrics()->gauge(
                $this->getPrefix() . '_max_connections',
                (float) $pool->getOption()->getMaxConnections(),
                [
                    'pool' => $poolName,
                    'worker' => (string) $workerId,
                ]
            );
        });

        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
