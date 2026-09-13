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
use Hyperf\Coordinator\Timer;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Sentry\Logs\Logs;
use Sentry\Metrics\TraceMetrics;
use Sentry\SentrySdk;
use Throwable;

class TelemetryFlushListener implements ListenerInterface
{
    private Timer $timer;

    private bool $ticking = false;

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature,
        ?Timer $timer = null,
    ) {
        $this->timer = $timer ?? new Timer();
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
        if ($this->ticking) {
            return;
        }

        if (! $this->feature->isMetricsEnabled() && ! $this->feature->isLogsEnabled()) {
            return;
        }

        $this->ticking = true;

        $this->timer->tick(
            $this->feature->getMetricsInterval(),
            function (): void {
                // End this tick coroutine's own runtime context (if any), so the
                // following flush operations target the global context aggregators.
                SentrySdk::endContext();

                try {
                    Logs::getInstance()->flush();
                } catch (Throwable) {
                }

                try {
                    TraceMetrics::getInstance()->flush();
                } catch (Throwable) {
                }
            }
        );
    }
}
