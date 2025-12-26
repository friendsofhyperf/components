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

use FriendsOfHyperf\Sentry\Constants as SentryConstants;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Metrics\CoroutineServerStats;
use FriendsOfHyperf\Sentry\Metrics\Event\MetricFactoryReady;
use FriendsOfHyperf\Sentry\Metrics\Traits\MetricSetter;
use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Support\System;
use Psr\Container\ContainerInterface;
use Sentry\Unit;
use Swoole\Server as SwooleServer;

use function FriendsOfHyperf\Sentry\metrics;

class OnMetricFactoryReady implements ListenerInterface
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
            MetricFactoryReady::class,
        ];
    }

    /**
     * @param object|MetricFactoryReady $event
     */
    public function process(object $event): void
    {
        if (! $this->feature->isDefaultMetricsEnabled()) {
            return;
        }

        $workerId = $event->workerId;
        $metrics = [
            'sys_load',
            'event_num',
            'signal_listener_num',
            'aio_task_num',
            'aio_worker_num',
            'c_stack_size',
            'coroutine_num',
            'coroutine_peak_num',
            'coroutine_last_cid',
            'connection_num',
            'accept_count',
            'close_count',
            'worker_num',
            'idle_worker_num',
            'tasking_num',
            'request_count',
            'timer_num',
            'timer_round',
            'swoole_timer_num',
            'swoole_timer_round',
            'metric_process_memory_usage',
            'metric_process_memory_peak_usage',
        ];

        $serverStatsFactory = null;

        if (! SentryConstants::$runningInCommand) {
            if ($this->container->has(SwooleServer::class) && $server = $this->container->get(SwooleServer::class)) {
                if ($server instanceof SwooleServer) {
                    $serverStatsFactory = fn (): array => $server->stats();
                }
            }

            if (! $serverStatsFactory) {
                $serverStatsFactory = fn (): array => $this->container->get(CoroutineServerStats::class)->toArray();
            }
        }

        $this->timer->tick(
            $this->feature->getMetricsInterval(),
            function () use ($metrics, $serverStatsFactory, $workerId) {
                $this->trySet('', $metrics, Co::stats(), $workerId);
                $this->trySet('timer_', $metrics, Timer::stats(), $workerId);

                if ($serverStatsFactory) {
                    $this->trySet('', $metrics, $serverStatsFactory(), $workerId);
                }

                if (class_exists('Swoole\Timer')) {
                    $this->trySet('swoole_timer_', $metrics, \Swoole\Timer::stats(), $workerId);
                }

                $load = sys_getloadavg();

                metrics()->gauge(
                    'sys_load',
                    round($load[0] / System::getCpuCoresNum(), 2),
                    ['worker' => (string) $workerId],
                );
                metrics()->gauge(
                    'metric_process_memory_usage',
                    memory_get_usage(true) / 1024 / 1024,
                    ['worker' => (string) $workerId],
                    Unit::megabyte()
                );
                metrics()->gauge(
                    'metric_process_memory_peak_usage',
                    memory_get_peak_usage(true) / 1024 / 1024,
                    ['worker' => (string) $workerId],
                    Unit::megabyte()
                );

                // metrics()->flush();
                Integration::flushEvents();
            }
        );
    }
}
