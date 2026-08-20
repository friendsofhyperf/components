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

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Metrics\Listener\OnBeforeHandle;
use Hyperf\Command\Command;
use Hyperf\Command\Event\BeforeHandle;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

beforeEach(function () {
    Constants::$runningInCommand = false;

    $this->timer = new FakeTimer();
    $this->container = $this->createMock(ContainerInterface::class);
    $this->feature = $this->createMock(Feature::class);
    $this->application = $this->createMock(Application::class);
    $this->command = $this->createMock(Command::class);
    $this->command->method('getApplication')->willReturn($this->application);

    $this->event = new BeforeHandle($this->command);
});

test('ticks only once when process is called twice', function () {
    $this->feature->method('isCommandMetricsEnabled')->willReturn(true);
    $this->feature->method('isDefaultMetricsEnabled')->willReturn(true);
    $this->feature->method('getMetricsInterval')->willReturn(10);
    $this->container->method('has')->willReturn(false);
    $this->application->method('isAutoExitEnabled')->willReturn(true);

    $listener = new OnBeforeHandle($this->container, $this->feature, $this->timer);
    $listener->process($this->event);
    $listener->process($this->event);

    expect($this->timer->ticks)->toHaveCount(1);
});

test('does not tick when metrics are disabled', function () {
    $this->feature->method('isCommandMetricsEnabled')->willReturn(false);
    $this->feature->method('isDefaultMetricsEnabled')->willReturn(false);
    $this->application->method('isAutoExitEnabled')->willReturn(true);

    $listener = new OnBeforeHandle($this->container, $this->feature, $this->timer);
    $listener->process($this->event);

    expect($this->timer->ticks)->toHaveCount(0);
});

test('does not tick when auto exit is disabled', function () {
    $this->application->method('isAutoExitEnabled')->willReturn(false);

    $listener = new OnBeforeHandle($this->container, $this->feature, $this->timer);
    $listener->process($this->event);

    expect($this->timer->ticks)->toHaveCount(0);
});
