<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\DatabaseLock;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\QueryException;
use Hyperf\Database\Query\Builder;
use Mockery as m;

test('can acquire lock when insert succeeds', function () {
    $builder = m::mock(Builder::class);
    $builder->shouldReceive('insert')->with(m::on(function ($data) {
        return $data['key'] === 'test_lock'
               && isset($data['owner'], $data['expiration']);
    }))->andReturn(true);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($builder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        private $mockConnection;

        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }
    };

    $lock->setConnection($connection);
    $result = $lock->acquire();

    expect($result)->toBeTrue();
});

test('can acquire lock when update succeeds after insert fails', function () {
    $insertBuilder = m::mock(Builder::class);
    $insertBuilder->shouldReceive('insert')->andThrow(new QueryException('', [], new Exception('Duplicate key')));

    $updateBuilder = m::mock(Builder::class);
    $updateBuilder->shouldReceive('where')->with('key', 'test_lock')->andReturnSelf();
    $updateBuilder->shouldReceive('where')->with(m::type('Closure'))->andReturnSelf();
    $updateBuilder->shouldReceive('update')->with(m::on(function ($data) {
        return isset($data['owner']) && isset($data['expiration']);
    }))->andReturn(1);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($insertBuilder, $updateBuilder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }
    };

    $lock->setConnection($connection);
    $result = $lock->acquire();

    expect($result)->toBeTrue();
});

test('acquire fails when both insert and update fail', function () {
    $insertBuilder = m::mock(Builder::class);
    $insertBuilder->shouldReceive('insert')->andThrow(new QueryException('', [], new Exception('Duplicate key')));

    $updateBuilder = m::mock(Builder::class);
    $updateBuilder->shouldReceive('where')->with('key', 'test_lock')->andReturnSelf();
    $updateBuilder->shouldReceive('where')->with(m::type('Closure'))->andReturnSelf();
    $updateBuilder->shouldReceive('update')->andReturn(0);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($insertBuilder, $updateBuilder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }
    };

    $lock->setConnection($connection);
    $result = $lock->acquire();

    expect($result)->toBeFalse();
});

test('can release lock when owned by current process', function () {
    $builder = m::mock(Builder::class);
    $builder->shouldReceive('where')->with('key', 'test_lock')->andReturnSelf();
    $builder->shouldReceive('first')->andReturn((object) ['owner' => 'owner123']);
    $builder->shouldReceive('where')->with('owner', 'owner123')->andReturnSelf();
    $builder->shouldReceive('delete')->andReturn(1);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($builder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }
    };

    $lock->setConnection($connection);
    $result = $lock->release();

    expect($result)->toBeTrue();
});

test('release fails when not owned by current process', function () {
    $builder = m::mock(Builder::class);
    $builder->shouldReceive('where')->with('key', 'test_lock')->andReturnSelf();
    $builder->shouldReceive('first')->andReturn((object) ['owner' => 'different_owner']);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($builder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }
    };

    $lock->setConnection($connection);
    $result = $lock->release();

    expect($result)->toBeFalse();
});

test('can force release lock', function () {
    $builder = m::mock(Builder::class);
    $builder->shouldReceive('where')->with('key', 'test_lock')->andReturnSelf();
    $builder->shouldReceive('delete')->andReturn(1);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($builder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }
    };

    $lock->setConnection($connection);
    $lock->forceRelease();

    expect(true)->toBeTrue(); // Just verify no exception is thrown
});

test('get current owner returns owner from database', function () {
    $builder = m::mock(Builder::class);
    $builder->shouldReceive('where')->with('key', 'test_lock')->andReturnSelf();
    $builder->shouldReceive('first')->andReturn((object) ['owner' => 'owner456']);

    $connection = m::mock(ConnectionInterface::class);
    $connection->shouldReceive('table')->with('locks')->andReturn($builder);

    $lock = new class('test_lock', 60, 'owner123') extends DatabaseLock {
        public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
        {
            $this->name = $name;
            $this->seconds = $seconds;
            $this->owner = $owner ?? 'default';
            $this->table = 'locks';
        }

        public function setConnection($connection): void
        {
            $this->connection = $connection;
        }

        public function testGetCurrentOwner()
        {
            return $this->getCurrentOwner();
        }
    };

    $lock->setConnection($connection);
    $result = $lock->testGetCurrentOwner();

    expect($result)->toBe('owner456');
});
