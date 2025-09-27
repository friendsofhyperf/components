<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\FileSystemLock;
use Hyperf\Cache\Driver\FileSystemDriver;
use Mockery as m;

test('can acquire lock when file does not exist', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('has')->with('test_lock')->andReturn(false);
    $store->shouldReceive('set')->with('test_lock', m::any(), 60)->andReturn(true);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->acquire();

    expect($result)->toBeTrue();
});

test('acquire returns false when file already exists', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('has')->with('test_lock')->andReturn(true);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->acquire();

    expect($result)->toBeFalse();
});

test('acquire returns false when set fails', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('has')->with('test_lock')->andReturn(false);
    $store->shouldReceive('set')->with('test_lock', m::any(), 60)->andReturn(false);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->acquire();

    expect($result)->toBeFalse();
});

test('can release lock when owned by current process', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('get')->with('test_lock')->andReturn('owner123');
    $store->shouldReceive('delete')->with('test_lock')->andReturn(true);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->release();

    expect($result)->toBeTrue();
});

test('release returns false when not owned by current process', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('get')->with('test_lock')->andReturn('different_owner');

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->release();

    expect($result)->toBeFalse();
});

test('can force release lock', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('delete')->with('test_lock')->andReturn(true);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $lock->forceRelease();

    expect(true)->toBeTrue(); // Just verify no exception is thrown
});

test('get current owner returns owner from file system', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('get')->with('test_lock')->andReturn('owner456');

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->testGetCurrentOwner();

    expect($result)->toBe('owner456');
});

test('get method executes callback and releases lock', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('has')->with('test_lock')->andReturn(false);
    $store->shouldReceive('set')->with('test_lock', m::any(), 60)->andReturn(true);
    $store->shouldReceive('get')->with('test_lock')->andReturn('owner123');
    $store->shouldReceive('delete')->with('test_lock')->andReturn(true);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $callbackExecuted = false;

    $result = $lock->get(function () use (&$callbackExecuted) {
        $callbackExecuted = true;
        return 'callback_result';
    });

    expect($result)->toBe('callback_result');
    expect($callbackExecuted)->toBeTrue();
});

test('get method returns false when lock cannot be acquired', function () {
    $store = m::mock(FileSystemDriver::class);
    $store->shouldReceive('has')->with('test_lock')->andReturn(true);

    $lock = new class('test_lock', 60, 'owner123') extends FileSystemLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
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

    $lock->setStore($store);
    $result = $lock->get();

    expect($result)->toBeFalse();
});
