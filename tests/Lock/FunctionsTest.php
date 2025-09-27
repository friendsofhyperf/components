<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\LockInterface;
use FriendsOfHyperf\Lock\LockFactory;

use function FriendsOfHyperf\Lock\lock;

test('lock function returns factory when no name provided', function () {
    $factory = $this->mock(LockFactory::class);
    $this->instance(LockFactory::class, $factory);

    $result = lock();

    expect($result)->toBe($factory);
});

test('lock function returns lock instance when name provided', function () {
    $lockInstance = $this->mock(LockInterface::class);
    $factory = $this->mock(LockFactory::class, function ($mock) use ($lockInstance) {
        $mock->shouldReceive('make')->with('foo', 0, null, 'default')->andReturn($lockInstance);
    });
    $this->instance(LockFactory::class, $factory);

    $result = lock('foo');

    expect($result)->toBe($lockInstance);
});

test('lock function passes all parameters correctly', function () {
    $lockInstance = $this->mock(LockInterface::class);
    $factory = $this->mock(LockFactory::class, function ($mock) use ($lockInstance) {
        $mock->shouldReceive('make')->with('mylock', 60, 'owner123', 'redis')->andReturn($lockInstance);
    });
    $this->instance(LockFactory::class, $factory);

    $result = lock('mylock', 60, 'owner123', 'redis');

    expect($result)->toBe($lockInstance);
});

test('lock function uses default values correctly', function () {
    $lockInstance = $this->mock(LockInterface::class);
    $factory = $this->mock(LockFactory::class, function ($mock) use ($lockInstance) {
        $mock->shouldReceive('make')->with('test', 30, null, 'default')->andReturn($lockInstance);
    });
    $this->instance(LockFactory::class, $factory);

    $result = lock('test', 30);

    expect($result)->toBe($lockInstance);
});
