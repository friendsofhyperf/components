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
use FriendsOfHyperf\Sentry\Metrics\Event\MetricFactoryReady;
use FriendsOfHyperf\Sentry\Metrics\Traits\MetricSetter;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sentry\Unit;
use Swoole\Server;

use function FriendsOfHyperf\Sentry\metrics;
use function Hyperf\Coroutine\defer;

class OnWorkerStart implements ListenerInterface
{
    use MetricSetter;

    private Timer $timer;

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature,
    ) {
        $this->timer = new Timer();
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    /**
     * @param object|BeforeWorkerStart $event
     */
    public function process(object $event): void
    {
        if (! $this->feature->isDefaultMetricsEnabled()) {
            return;
        }

        $workerId = $event->workerId;
        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new MetricFactoryReady($workerId));

        // The following metrics MUST be collected in worker.
        $metrics = [
            'worker_request_count',
            'worker_dispatch_count',
            'memory_usage',
            'memory_peak_usage',
            'gc_runs',
            'gc_collected',
            'gc_threshold',
            'gc_roots',
            'ru_oublock',
            'ru_inblock',
            'ru_msgsnd',
            'ru_msgrcv',
            'ru_maxrss',
            'ru_ixrss',
            'ru_idrss',
            'ru_minflt',
            'ru_majflt',
            'ru_nsignals',
            'ru_nvcsw',
            'ru_nivcsw',
            'ru_nswap',
            'ru_utime_tv_usec',
            'ru_utime_tv_sec',
            'ru_stime_tv_usec',
            'ru_stime_tv_sec',
        ];

        $timerId = $this->timer->tick($this->feature->getMetricsInterval(), function () use ($metrics, $event) {
            defer(fn () => metrics()->flush());

            $server = $this->container->get(Server::class);
            $serverStats = $server->stats();
            $this->trySet('gc_', $metrics, gc_status());
            $this->trySet('', $metrics, getrusage());

            metrics()->gauge(
                'worker_request_count',
                (float) $serverStats['worker_request_count'],
                ['worker' => (string) ($event->workerId ?? 0)],
            );
            metrics()->gauge(
                'worker_dispatch_count',
                (float) $serverStats['worker_dispatch_count'],
                ['worker' => (string) ($event->workerId ?? 0)],
            );
            metrics()->gauge(
                'memory_usage',
                (float) memory_get_usage(),
                ['worker' => (string) ($event->workerId ?? 0)],
                Unit::byte()
            );
            metrics()->gauge(
                'memory_peak_usage',
                (float) memory_get_peak_usage(),
                ['worker' => (string) ($event->workerId ?? 0)],
                Unit::byte()
            );
        });

        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
