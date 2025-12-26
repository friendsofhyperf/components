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
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Metrics\Event\MetricFactoryReady;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

use function FriendsOfHyperf\Sentry\metrics;

class QueueWatcher implements ListenerInterface
{
    private Timer $timer;

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
            MetricFactoryReady::class,
        ];
    }

    /**
     * @param object|MetricFactoryReady $event
     */
    public function process(object $event): void
    {
        if (! $this->feature->isQueueMetricsEnabled()) {
            return;
        }

        $this->timer->tick(
            $this->feature->getMetricsInterval(),
            function () {
                $config = $this->container->get(ConfigInterface::class);
                $queues = array_keys($config->get('async_queue', []));

                foreach ($queues as $name) {
                    $queue = $this->container->get(DriverFactory::class)->get($name);
                    $info = $queue->info();

                    metrics()->gauge(
                        'queue_waiting',
                        (float) $info['waiting'],
                        ['queue' => $name]
                    );
                    metrics()->gauge(
                        'queue_delayed',
                        (float) $info['delayed'],
                        ['queue' => $name]
                    );
                    metrics()->gauge(
                        'queue_failed',
                        (float) $info['failed'],
                        ['queue' => $name]
                    );
                    metrics()->gauge(
                        'queue_timeout',
                        (float) $info['timeout'],
                        ['queue' => $name]
                    );
                }

                // metrics()->flush();
                Integration::flushEvents();
            }
        );
    }
}
