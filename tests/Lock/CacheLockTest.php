<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

use FriendsOfHyperf\Lock\Driver\CacheLock;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Context\ApplicationContext;
use Mockery as m;

test('can acquire lock when not exists', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('has')->with('test_lock')->andReturn(false);
    $cache->shouldReceive('set')->with('test_lock', m::any(), 60)->andReturn(true);

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $result = $lock->acquire();

    expect($result)->toBeTrue();
});

test('acquire returns false when lock already exists', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('has')->with('test_lock')->andReturn(true);

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $result = $lock->acquire();

    expect($result)->toBeFalse();
});

test('can release lock when owned by current process', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('get')->with('test_lock')->andReturn('owner123');
    $cache->shouldReceive('delete')->with('test_lock')->andReturn(true);

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $result = $lock->release();

    expect($result)->toBeTrue();
});

test('release returns false when not owned by current process', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('get')->with('test_lock')->andReturn('different_owner');

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $result = $lock->release();

    expect($result)->toBeFalse();
});

test('can force release lock', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('delete')->with('test_lock')->andReturn(true);

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $lock->forceRelease();

    expect(true)->toBeTrue(); // Just verify no exception is thrown
});

test('get current owner returns cache value', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('get')->with('test_lock')->andReturn('owner456');

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new class('test_lock', 60, 'owner123') extends CacheLock {
        public function testGetCurrentOwner()
        {
            return $this->getCurrentOwner();
        }
    };
    
    $result = $lock->testGetCurrentOwner();

    expect($result)->toBe('owner456');
});

test('can use custom driver in constructor', function () {
    $cache = m::mock(DriverInterface::class);
    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('redis')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123', ['driver' => 'redis']);
    
    expect($lock)->toBeInstanceOf(CacheLock::class);
});

test('get method executes callback and releases lock', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('has')->with('test_lock')->andReturn(false);
    $cache->shouldReceive('set')->with('test_lock', m::any(), 60)->andReturn(true);
    $cache->shouldReceive('get')->with('test_lock')->andReturn('owner123');
    $cache->shouldReceive('delete')->with('test_lock')->andReturn(true);

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $callbackExecuted = false;
    
    $result = $lock->get(function () use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });

    expect($result)->toBe('callback_result');
    expect($callbackExecuted)->toBeTrue();
});

test('get method returns false when lock cannot be acquired', function () {
    $cache = m::mock(DriverInterface::class);
    $cache->shouldReceive('has')->with('test_lock')->andReturn(true);

    $cacheManager = m::mock(CacheManager::class);
    $cacheManager->shouldReceive('getDriver')->with('default')->andReturn($cache);
    
    $this->instance(CacheManager::class, $cacheManager);

    $lock = new CacheLock('test_lock', 60, 'owner123');
    $result = $lock->get();

    expect($result)->toBeFalse();
});