<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\RateLimit\Exception\RateLimitException;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;
use Hyperf\Redis\Redis;
use Mockery as m;
use Psr\Container\ContainerInterface;

test('factory can create fixed window limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redis = m::mock(Redis::class);

    $container->shouldReceive('get')
        ->with(Redis::class)
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make('fixed_window');

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\FixedWindowRateLimiter::class);
});

test('factory can create sliding window limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redis = m::mock(Redis::class);

    $container->shouldReceive('get')
        ->with(Redis::class)
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make('sliding_window');

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\SlidingWindowRateLimiter::class);
});

test('factory can create token bucket limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redis = m::mock(Redis::class);

    $container->shouldReceive('get')
        ->with(Redis::class)
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make('token_bucket');

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\TokenBucketRateLimiter::class);
});

test('factory can create leaky bucket limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redis = m::mock(Redis::class);

    $container->shouldReceive('get')
        ->with(Redis::class)
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make('leaky_bucket');

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\LeakyBucketRateLimiter::class);
});

test('factory throws exception for unsupported algorithm', function () {
    $container = m::mock(ContainerInterface::class);
    $redis = m::mock(Redis::class);

    $container->shouldReceive('get')
        ->with(Redis::class)
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);

    expect(fn () => $factory->make('invalid_algorithm'))
        ->toThrow(RateLimitException::class);
});

test('factory caches limiter instances', function () {
    $container = m::mock(ContainerInterface::class);
    $redis = m::mock(Redis::class);

    $container->shouldReceive('get')
        ->with(Redis::class)
        ->once()
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter1 = $factory->make('fixed_window');
    $limiter2 = $factory->make('fixed_window');

    expect($limiter1)->toBe($limiter2);
});
