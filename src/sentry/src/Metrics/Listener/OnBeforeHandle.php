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

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Metrics\Traits\MetricSetter;
use FriendsOfHyperf\Sentry\SentryContext;
use Hyperf\Command\Event\BeforeHandle;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\Unit;

use function FriendsOfHyperf\Sentry\metrics;

class OnBeforeHandle implements ListenerInterface
{
    use MetricSetter;

    protected Timer $timer;

    public function __construct(protected Feature $feature)
    {
        $this->timer = new Timer();
    }

    public function listen(): array
    {
        return [
            BeforeHandle::class,
        ];
    }

    /**
     * @param object|BeforeHandle $event
     */
    public function process(object $event): void
    {
        if (
            ! $event instanceof BeforeHandle
            || SentryContext::getCronCheckInId() // Prevent duplicate metrics in cron job.
            || ! $event->getCommand()->getApplication()->isAutoExitEnabled() // Only enable in the command with auto exit.
            || ! $this->feature->isCommandMetricsEnabled()
        ) {
            return;
        }

        Constants::$runningInCommand = true;

        if (! $this->feature->isDefaultMetricsEnabled()) {
            return;
        }

        // The following metrics MUST be collected in worker.
        $metrics = [
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

        $timerId = $this->timer->tick($this->feature->getMetricsInterval(), function () use ($metrics) {
            defer(fn () => metrics()->flush());

            $this->trySet('gc_', $metrics, gc_status());
            $this->trySet('', $metrics, getrusage());

            metrics()->gauge(
                'memory_usage',
                (float) memory_get_usage(),
                ['worker' => '0'],
                Unit::byte()
            );
            metrics()->gauge(
                'memory_peak_usage',
                (float) memory_get_peak_usage(),
                ['worker' => '0'],
                Unit::byte()
            );
        });

        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(\Hyperf\Coordinator\Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
