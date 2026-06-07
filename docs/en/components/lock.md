# Lock

The atomic lock component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/lock
```

The default driver uses Redis. Install the optional dependency required by the driver you use:

| Driver | Configuration name | Optional dependency |
| --- | --- | --- |
| `RedisLock` | `default` | `hyperf/redis` |
| `FileSystemLock` | `file` | `hyperf/cache` |
| `DatabaseLock` | `database` | `hyperf/db-connection` |
| `CoroutineLock` | `co` | None |
| `CacheLock` | Not published | `hyperf/cache` |

Publish the configuration file:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

When using the database driver, also publish and run the lock migration:

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i migrations
php bin/hyperf.php migrate
```

Before running the published migration, change its `value` column to `owner`. The current
`DatabaseLock` implementation reads and writes an `owner` column.

## Configuration

The published `config/autoload/lock.php` contains the `default`, `file`, `database`, and `co`
configurations. Each configuration selects a driver class and passes its `constructor` options to
that driver. The fourth argument of `lock()` and `LockFactory::make()` is the configuration name,
not a driver class name.

The factory throws `InvalidArgumentException` when the selected `lock.<driver>` configuration does
not exist. You may add custom configurations whose driver implements
`FriendsOfHyperf\Lock\Driver\LockInterface`.

## Creating Locks

Import and call the namespaced helper:

```php
use function FriendsOfHyperf\Lock\lock;

$lock = lock(name: 'foo', seconds: 10, owner: null, driver: 'default');

if ($lock->get()) {
    try {
        // The lock was acquired.
    } finally {
        $lock->release();
    }
}
```

`lock()` returns the `LockFactory` when called without a name. Otherwise, it returns a
`LockInterface` instance. Its parameters are:

- `name`: lock name.
- `seconds`: requested lock lifetime in seconds; defaults to `0`.
- `owner`: optional ownership identifier; a random owner is generated when omitted.
- `driver`: configuration name under `lock`; defaults to `default`.

TTL behavior is driver-specific. In particular, `DatabaseLock` stores a one-day expiration when
`seconds` is `0` or negative.

## Lock Operations

`LockInterface` exposes `get()`, `block()`, `release()`, `owner()`, `forceRelease()`, `refresh()`,
`isExpired()`, and `getRemainingLifetime()`.

`get()` attempts acquisition once and returns a boolean when no callback is given. With a callback,
the callback result is returned and the lock is released in a `finally` block:

```php
$result = lock('foo', 10)->get(function () {
    return 'completed';
});
```

`block()` retries until the lock is acquired or the wait limit is reached. It throws
`LockTimeoutException` on timeout. A callback is also released automatically:

```php
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

try {
    $result = lock('foo', 10)->block(5, function () {
        return 'completed';
    });
} catch (LockTimeoutException $exception) {
    // The lock was not acquired within five seconds.
}
```

`release()` only releases a lock owned by the current owner. `forceRelease()` ignores ownership.
`refresh($ttl)` refreshes the expiration only when the driver can do so; it returns `false` for a
non-positive TTL. `isExpired()` and `getRemainingLifetime()` report expiration tracked by the lock
instance.

## Annotations

### Property Injection

`#[Lock]` targets properties and injects a lock instance during application boot. Its parameters
are `name`, `seconds`, `owner`, and `driver`.

```php
use FriendsOfHyperf\Lock\Annotation\Lock;
use FriendsOfHyperf\Lock\Driver\LockInterface;

class Foo
{
    #[Lock(name: 'foo', seconds: 10, driver: 'default')]
    protected LockInterface $lock;
}
```

### Blocking Methods

`#[Blockable]` targets methods. When `seconds` is greater than `0`, it creates a lock and waits up
to `seconds` for acquisition, then automatically releases the lock after the method returns or
throws. `ttl` is the lock lifetime, and `driver` selects the lock configuration. `prefix` and
`value` are formatted with the method arguments to build the lock name.

`Blockable` uses `Hyperf\Cache\Helper\StringHelper`, so install `hyperf/cache` before using this
annotation.

```php
use FriendsOfHyperf\Lock\Annotation\Blockable;

class Foo
{
    #[Blockable(prefix: 'user', value: '#{id}', seconds: 5, ttl: 30, driver: 'default')]
    public function update(int $id): void
    {
    }
}
```
