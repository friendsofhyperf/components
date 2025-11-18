<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\RateLimit\Algorithm;
use FriendsOfHyperf\RateLimit\RateLimiterFactory;
use Hyperf\Redis\RedisProxy;
use Hyperf\Redis\RedisFactory;
use Mockery as m;
use Psr\Container\ContainerInterface;

test('factory can create fixed window limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redisFactory = m::mock(RedisFactory::class);
    $redis = m::mock(RedisProxy::class);

    $container->shouldReceive('get')
        ->with(RedisFactory::class)
        ->andReturn($redisFactory);
    $redisFactory->shouldReceive('get')
        ->with('default')
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make(Algorithm::FIXED_WINDOW);

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\FixedWindowRateLimiter::class);
});

test('factory can create sliding window limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redisFactory = m::mock(RedisFactory::class);
    $redis = m::mock(RedisProxy::class);

    $container->shouldReceive('get')
        ->with(RedisFactory::class)
        ->andReturn($redisFactory);
    $redisFactory->shouldReceive('get')
        ->with('default')
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make(Algorithm::SLIDING_WINDOW);

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\SlidingWindowRateLimiter::class);
});

test('factory can create token bucket limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redisFactory = m::mock(RedisFactory::class);
    $redis = m::mock(RedisProxy::class);

    $container->shouldReceive('get')
        ->with(RedisFactory::class)
        ->andReturn($redisFactory);
    $redisFactory->shouldReceive('get')
        ->with('default')
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make(Algorithm::TOKEN_BUCKET);

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\TokenBucketRateLimiter::class);
});

test('factory can create leaky bucket limiter', function () {
    $container = m::mock(ContainerInterface::class);
    $redisFactory = m::mock(RedisFactory::class);
    $redis = m::mock(RedisProxy::class);

    $container->shouldReceive('get')
        ->with(RedisFactory::class)
        ->andReturn($redisFactory);
    $redisFactory->shouldReceive('get')
        ->with('default')
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter = $factory->make(Algorithm::LEAKY_BUCKET);

    expect($limiter)->toBeInstanceOf(FriendsOfHyperf\RateLimit\Algorithm\LeakyBucketRateLimiter::class);
});

test('factory caches limiter instances', function () {
    $container = m::mock(ContainerInterface::class);
    $redisFactory = m::mock(RedisFactory::class);
    $redis = m::mock(RedisProxy::class);

    $container->shouldReceive('get')
        ->with(RedisFactory::class)
        ->once()
        ->andReturn($redisFactory);
    $redisFactory->shouldReceive('get')
        ->with('default')
        ->once()
        ->andReturn($redis);

    $factory = new RateLimiterFactory($container);
    $limiter1 = $factory->make(Algorithm::FIXED_WINDOW);
    $limiter2 = $factory->make(Algorithm::FIXED_WINDOW);

    expect($limiter1)->toBe($limiter2);
});
