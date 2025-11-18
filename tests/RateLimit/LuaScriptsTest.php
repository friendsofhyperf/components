<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\RateLimit\Storage\LuaScripts;

test('fixed window script returns valid lua script', function () {
    $script = LuaScripts::fixedWindow();

    expect($script)->toBeString();
    expect($script)->toContain('redis.call');
    expect($script)->toContain('incr');
    expect($script)->toContain('expire');
});

test('sliding window script returns valid lua script', function () {
    $script = LuaScripts::slidingWindow();

    expect($script)->toBeString();
    expect($script)->toContain('zremrangebyscore');
    expect($script)->toContain('zcard');
    expect($script)->toContain('zadd');
});

test('token bucket script returns valid lua script', function () {
    $script = LuaScripts::tokenBucket();

    expect($script)->toBeString();
    expect($script)->toContain('hmget');
    expect($script)->toContain('tokens');
    expect($script)->toContain('last_refill');
});

test('leaky bucket script returns valid lua script', function () {
    $script = LuaScripts::leakyBucket();

    expect($script)->toBeString();
    expect($script)->toContain('hmget');
    expect($script)->toContain('water');
    expect($script)->toContain('last_leak');
});
