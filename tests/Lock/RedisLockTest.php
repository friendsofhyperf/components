<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

use FriendsOfHyperf\Lock\Driver\LuaScripts;
use FriendsOfHyperf\Lock\Driver\RedisLock;
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;
use Hyperf\Redis\RedisProxy;
use Mockery as m;

test('can acquire lock with expiration', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('set')->with('test_lock', m::any(), ['NX', 'EX' => 60])->andReturn(true);
    
    $lock = new class('test_lock', 60, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->acquire();

    expect($result)->toBeTrue();
});

test('can acquire lock without expiration', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('setNX')->with('test_lock', m::any())->andReturn(true);

    $lock = new class('test_lock', 0, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->acquire();

    expect($result)->toBeTrue();
});

test('acquire returns false when lock already exists', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('setNX')->with('test_lock', m::any())->andReturn(false);

    $lock = new class('test_lock', 0, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->acquire();

    expect($result)->toBeFalse();
});

test('can release lock successfully', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('eval')->with(LuaScripts::releaseLock(), ['test_lock', 'owner123'], 1)->andReturn(1);

    $lock = new class('test_lock', 60, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->release();

    expect($result)->toBeTrue();
});

test('release returns false when script returns 0', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('eval')->with(LuaScripts::releaseLock(), ['test_lock', 'owner123'], 1)->andReturn(0);

    $lock = new class('test_lock', 60, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->release();

    expect($result)->toBeFalse();
});

test('can force release lock', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('del')->with('test_lock')->andReturn(1);

    $lock = new class('test_lock', 60, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $lock->forceRelease();

    expect(true)->toBeTrue(); // Just verify no exception is thrown
});

test('get current owner returns owner value', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('get')->with('test_lock')->andReturn('owner456');

    $lock = new class('test_lock', 60, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
        
        public function testGetCurrentOwner()
        {
            return $this->getCurrentOwner();
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->testGetCurrentOwner();

    expect($result)->toBe('owner456');
});

test('get method executes callback and releases lock', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('setNX')->with('test_lock', m::any())->andReturn(true);
    $redis->shouldReceive('eval')->with(m::any(), m::any(), m::any())->andReturn(1);

    $lock = new class('test_lock', 0, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $callbackExecuted = false;
    
    $result = $lock->get(function () use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });

    expect($result)->toBe('callback_result');
    expect($callbackExecuted)->toBeTrue();
});

test('get method returns false when lock cannot be acquired', function () {
    $redis = m::mock(RedisProxy::class);
    $redis->shouldReceive('setNX')->with('test_lock', m::any())->andReturn(false);

    $lock = new class('test_lock', 0, 'owner123') extends RedisLock {
        public function __construct(string $name, int $seconds, ?string $owner = null)
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
        }
        
        public function setStore($store): void
        {
            $this->store = $store;
        }
    };
    
    $lock->setStore($redis);
    $result = $lock->get();

    expect($result)->toBeFalse();
});