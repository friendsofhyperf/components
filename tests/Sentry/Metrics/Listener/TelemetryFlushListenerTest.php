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
use FriendsOfHyperf\Sentry\Metrics\Listener\TelemetryFlushListener;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\Timer;
use Mockery as m;
use Psr\Container\ContainerInterface;

class FakeTimer extends Timer
{
    public int $tickCount = 0;

    /**
     * @var array<int, callable>
     */
    public array $closures = [];

    public function tick(float $timeout, callable $closure, string $identifier = Constants::WORKER_EXIT): int
    {
        ++$this->tickCount;
        $this->closures[$this->tickCount] = $closure;

        return $this->tickCount;
    }
}

beforeEach(function () {
    $this->feature = m::mock(Feature::class);
    $this->feature->shouldReceive('getMetricsInterval')->andReturn(10);

    $this->container = m::mock(ContainerInterface::class);
    $this->timer = new FakeTimer();
});

afterEach(function () {
    m::close();
});

test('process schedules a single tick for repeated calls', function () {
    $this->feature->shouldReceive('isMetricsEnabled')->andReturn(true);
    $this->feature->shouldReceive('isLogsEnabled')->andReturn(true);

    $listener = new TelemetryFlushListener($this->container, $this->feature, $this->timer);

    $listener->process(new MetricFactoryReady());
    $listener->process(new MetricFactoryReady());

    expect($this->timer->tickCount)->toBe(1);
});

test('saved tick closure can be invoked without throwing', function () {
    $this->feature->shouldReceive('isMetricsEnabled')->andReturn(true);
    $this->feature->shouldReceive('isLogsEnabled')->andReturn(true);

    $listener = new TelemetryFlushListener($this->container, $this->feature, $this->timer);
    $listener->process(new MetricFactoryReady());

    $closure = $this->timer->closures[1];

    $closure(false);

    expect(true)->toBeTrue();
});

test('no tick is scheduled when logs and metrics are disabled', function () {
    $this->feature->shouldReceive('isMetricsEnabled')->andReturn(false);
    $this->feature->shouldReceive('isLogsEnabled')->andReturn(false);

    $listener = new TelemetryFlushListener($this->container, $this->feature, $this->timer);

    $listener->process(new MetricFactoryReady());

    expect($this->timer->tickCount)->toBe(0);
});
