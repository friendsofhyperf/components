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
use Hyperf\Coordinator\Timer;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sentry\Unit;

use function FriendsOfHyperf\Sentry\metrics;

class OnCoroutineServerStart implements ListenerInterface
{
    use MetricSetter;

    private Timer $timer;

    private bool $running = false;

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature,
    ) {
        $this->timer = new Timer();
    }

    public function listen(): array
    {
        return [
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * @param object|MainCoroutineServerStart $event
     */
    public function process(object $event): void
    {
        if (! $this->feature->isMetricsEnabled()) {
            return;
        }

        if ($this->running) {
            return;
        }

        $this->running = true;

        $eventDispatcher = $this->container->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(new MetricFactoryReady());

        if (! $this->feature->isDefaultMetricsEnabled()) {
            return;
        }

        // The following metrics MUST be collected in worker.
        $metrics = [
            // 'worker_request_count',
            // 'worker_dispatch_count',
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

        $this->timer->tick($this->feature->getMetricsInterval(), function ($isClosing = false) use ($metrics) {
            if ($isClosing) {
                return Timer::STOP;
            }

            $this->trySet('gc_', $metrics, gc_status());
            $this->trySet('', $metrics, getrusage());

            metrics()->gauge(
                'memory_usage',
                memory_get_usage(true) / 1024 / 1024,
                ['worker' => '0'],
                Unit::megabyte()
            );
            metrics()->gauge(
                'memory_peak_usage',
                memory_get_peak_usage(true) / 1024 / 1024,
                ['worker' => '0'],
                Unit::megabyte()
            );

            metrics()->flush();
        });
    }
}
