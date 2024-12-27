# Lock

The Hyperf atomic lock component.

## Installation

- Install

```shell
composer require friendsofhyperf/lock
```

- Publish configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

## Usage

You can use the `lock()` method to create and manage locks:

```php
$lock = lock($name = 'foo', $seconds = 10, $owner = null);

if ($lock->get()) {
    // Acquired the lock for 10 seconds...

    $lock->release();
}
```

The `get` method can also accept a closure. After the closure executes, the lock will be automatically released:

```php
lock('foo')->get(function () {
    // Acquired an indefinite lock and automatically released it...
});
```

If the lock is unavailable when requested, you can specify a number of seconds to wait. If the lock cannot be acquired within the specified timeout, a `FriendsOfHyperf\Lock\Exception\LockTimeoutException` will be thrown.

```php
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

$lock = lock('foo', 10);

try {
    $lock->block(5);

    // Acquired the lock after waiting up to 5 seconds...
} catch (LockTimeoutException $e) {
    // Failed to acquire the lock...
} finally {
    optional($lock)->release();
}

lock('foo', 10)->block(5, function () {
    // Acquired the lock after waiting up to 5 seconds...
});
```

Annotation-based approach:

```php
use FriendsOfHyperf\Lock\Annotation\Lock;
use FriendsOfHyperf\Lock\Driver\LockInterface;

class Foo
{
    #[Lock(name:"foo", seconds:10)]
    protected LockInterface $lock;

    public function bar()
    {
        $this->lock->get(function () {
            // Acquired an indefinite lock and automatically released it...
        });
    }
}
```