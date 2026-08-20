<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Sentry\Metrics\Listener;

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Metrics\Event\MetricFactoryReady;
use FriendsOfHyperf\Sentry\Metrics\Listener\QueueWatcher;
use Psr\Container\ContainerInterface;

beforeEach(function () {
    $this->timer = new FakeTimer();
    $this->container = $this->createMock(ContainerInterface::class);
    $this->feature = $this->createMock(Feature::class);

    $this->event = new MetricFactoryReady();
});

test('ticks only once when process is called twice', function () {
    $this->feature->method('isQueueMetricsEnabled')->willReturn(true);
    $this->feature->method('getMetricsInterval')->willReturn(10);

    $listener = new QueueWatcher($this->container, $this->feature, $this->timer);
    $listener->process($this->event);
    $listener->process($this->event);

    expect($this->timer->ticks)->toHaveCount(1);
});

test('does not tick when queue metrics are disabled', function () {
    $this->feature->method('isQueueMetricsEnabled')->willReturn(false);

    $listener = new QueueWatcher($this->container, $this->feature, $this->timer);
    $listener->process($this->event);

    expect($this->timer->ticks)->toHaveCount(0);
});
