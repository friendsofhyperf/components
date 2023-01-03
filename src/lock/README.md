# Lock

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/lock)](https://packagist.org/packages/friendsofhyperf/lock)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/lock)](https://packagist.org/packages/friendsofhyperf/lock)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/lock)](https://github.com/friendsofhyperf/lock)

The lock component for Hyperf. [中文说明](README_CN.md)

## Installation

- Request

```bash
composer require "friendsofhyperf/lock"
```

- Publish

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

## Usage

You may create and manage locks using the `lock()` method:

```php
$lock = lock($name = 'foo', $seconds = 10, $owner = null);

if ($lock->get()) {
    // Lock acquired for 10 seconds...

    $lock->release();
}
```

The `get` method also accepts a closure. After the closure is executed, Will automatically release the lock:

```php
lock('foo')->get(function () {
    // Lock acquired indefinitely and automatically released...
});
```

If the lock is not available at the moment you request it, you may instruct the lock to wait for a specified number of seconds. If the lock can not be acquired within the specified time limit, an `FriendsOfHyperf\Lock\Exception\LockTimeoutException` will be thrown:

```php
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

$lock = lock('foo', 10);

try {
    $lock->block(5);

    // Lock acquired after waiting maximum of 5 seconds...
} catch (LockTimeoutException $e) {
    // Unable to acquire lock...
} finally {
    optional($lock)->release();
}

lock('foo', 10)->block(5, function () {
    // Lock acquired after waiting maximum of 5 seconds...
});
```
