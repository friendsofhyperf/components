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
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Sentry\Metrics\TraceMetrics;

use function Hyperf\Coroutine\defer;

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
        if (! $this->feature->isMetricsEnabled()) {
            return;
        }

        $timerId = $this->timer->tick($this->feature->getMetricsInterval(), function () {
            defer(fn () => TraceMetrics::getInstance()->flush());

            $config = $this->container->get(ConfigInterface::class);
            $queues = array_keys($config->get('async_queue', []));

            foreach ($queues as $name) {
                $queue = $this->container->get(DriverFactory::class)->get($name);
                $info = $queue->info();

                TraceMetrics::getInstance()->gauge(
                    'queue_waiting',
                    (float) $info['waiting'],
                    ['queue' => $name]
                );
                TraceMetrics::getInstance()->gauge(
                    'queue_delayed',
                    (float) $info['delayed'],
                    ['queue' => $name]
                );
                TraceMetrics::getInstance()->gauge(
                    'queue_failed',
                    (float) $info['failed'],
                    ['queue' => $name]
                );
                TraceMetrics::getInstance()->gauge(
                    'queue_timeout',
                    (float) $info['timeout'],
                    ['queue' => $name]
                );
            }
        });

        Coroutine::create(function () use ($timerId) {
            CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
            $this->timer->clear($timerId);
        });
    }
}
