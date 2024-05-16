<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Support\Timebox;
use Mockery as m;

test('test MakeExecutesCallback', function () {
    $callback = function () {
        $this->assertTrue(true);
    };

    (new Timebox())->call($callback, 0);
});

test('test MakeWaitsForMicroseconds', function () {
    $mock = m::spy(Timebox::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $mock->shouldReceive('usleep')->once();

    $mock->call(function () {}, 10000);

    $mock->shouldHaveReceived('usleep')->once();
});

test('test MakeShouldNotSleepWhenEarlyReturnHasBeenFlagged', function () {
    $mock = m::spy(Timebox::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $mock->call(function ($timebox) {
        $timebox->returnEarly();
    }, 10000);

    $mock->shouldNotHaveReceived('usleep');
});

test('test MakeShouldSleepWhenDontEarlyReturnHasBeenFlagged', function () {
    $mock = m::spy(Timebox::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $mock->shouldReceive('usleep')->once();

    $mock->call(function ($timebox) {
        $timebox->returnEarly();
        $timebox->dontReturnEarly();
    }, 10000);

    $mock->shouldHaveReceived('usleep')->once();
});

test('test MakeWaitsForMicrosecondsWhenExceptionIsThrown', function () {
    $mock = m::spy(Timebox::class)->shouldAllowMockingProtectedMethods()->makePartial();
    $mock->shouldReceive('usleep')->once();

    try {
        $this->expectExceptionMessage('Exception within Timebox callback.');

        $mock->call(function () {
            throw new Exception('Exception within Timebox callback.');
        }, 10000);
    } finally {
        $mock->shouldHaveReceived('usleep')->once();
    }
});

test('test MakeShouldNotSleepWhenEarlyReturnHasBeenFlaggedAndExceptionIsThrown', function () {
    $mock = m::spy(Timebox::class)->shouldAllowMockingProtectedMethods()->makePartial();

    try {
        $this->expectExceptionMessage('Exception within Timebox callback.');

        $mock->call(function ($timebox) {
            $timebox->returnEarly();
            throw new Exception('Exception within Timebox callback.');
        }, 10000);
    } finally {
        $mock->shouldNotHaveReceived('usleep');
    }
});
