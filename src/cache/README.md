# Cache

[中文说明](README_CN.md)

## Introduction

`friendsofhyperf/cache` wraps the drivers provided by `hyperf/cache` with a
Laravel-style repository API. It supports PSR-16 operations, named stores,
facade access, cache events, macros, and stale-while-revalidate caching.

## Installation

```shell
composer require friendsofhyperf/cache
```

The component requires Hyperf 3.2. Its `ConfigProvider` registers
`FriendsOfHyperf\Cache\Contract\Factory` and
`FriendsOfHyperf\Cache\Contract\Repository` in the container.

## Configuration

This component does not publish a separate configuration file. Configure
drivers and named stores through `hyperf/cache`; `CacheManager::store($name)`
passes the name to Hyperf's cache manager. The default repository resolves the
`default` store.

## Accessing a Repository

### Dependency Injection

Inject `Contract\Repository` for the default store:

```php
namespace App\Controller;

use FriendsOfHyperf\Cache\Contract\Repository;

class IndexController
{
    public function __construct(private Repository $cache)
    {
    }

    public function index(): mixed
    {
        return $this->cache->remember('users', 60, function () {
            return [];
        });
    }
}
```

Inject `Contract\Factory` when a named store is required:

```php
use FriendsOfHyperf\Cache\Contract\Factory;

$cache = $factory->store('redis');
```

### Facade

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::remember('users', 60, function () {
    return [];
});

$users = Cache::store('redis')->get('users');
```

`Cache::driver($name)` is an alias of `Cache::store($name)`.
`Cache::resolve($name)` creates a new repository instead of returning the
manager's cached repository instance.

## Core Operations

The repository implements `Psr\SimpleCache\CacheInterface`, so `get()`, `set()`,
`delete()`, `clear()`, `getMultiple()`, `setMultiple()`, `deleteMultiple()`,
and `has()` are available. It also provides these extensions:

| Method | Behavior |
| --- | --- |
| `get($key, $default = null)` | Retrieves one item; a callable default is evaluated on a miss. Passing an array delegates to `many()`. |
| `put($key, $value, $ttl = null)` | Stores one item; `null` means forever and a non-positive TTL deletes the key. Passing an associative array delegates to `putMany()`, with the second argument used as its TTL. |
| `putMany($values, $ttl = null)` | Stores multiple items; a non-positive TTL deletes their keys. |
| `forever($key, $value)` | Stores one item without a TTL. |
| `add($key, $value, $ttl = null)` | Stores the item only when `get($key)` returns `null`. |
| `many($keys)` | Retrieves multiple keys; associative input may provide per-key defaults. |
| `pull($key, $default = null)` | Retrieves and then deletes an item. |
| `remember($key, $ttl, Closure $callback)` | Returns the cached value or stores the callback result with a TTL. |
| `rememberForever($key, Closure $callback)` / `sear(...)` | Returns the cached value or stores the callback result forever. |
| `increment($key, $value = 1)` / `decrement(...)` | Reads, adjusts, and stores an integer value without a TTL. |
| `flush()` | Alias of `clear()`. |
| `missing($key)` | Inverse of `has($key)`. |
| `getDriver()` / `getStore()` | Returns the underlying Hyperf `DriverInterface`. |

TTL values accepted by the extended repository methods may be seconds,
`DateInterval`, or `DateTimeInterface`.

::: warning Behavioral boundaries
The repository treats a cached `null` as a miss. `add()`, `pull()`, and the
increment/decrement methods are implemented as separate operations, so they are
not atomic. Although `pull()` accepts a `$default` argument, the current
implementation does not pass it to `get()` and returns `null` for a missing
key.
:::

## Stale-While-Revalidate

`flexible()` accepts a two-item TTL array: the first value is the fresh period
and the second is the storage TTL used for both the cached value and its
internal creation timestamp.

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::flexible('users', [30, 300], function () {
    return [];
}, [
    'seconds' => 10,
    'owner' => 'users-refresh',
]);
```

On a miss, the callback runs immediately. During the fresh period, the cached
value is returned. After the fresh period, the stale value is returned and a
deferred callback attempts to refresh it under a lock. A refresh is skipped if
another process has already updated the creation timestamp.

Install both optional dependencies before using `flexible()`:

```shell
composer require friendsofhyperf/lock hyperf/coroutine
```

The optional lock array accepts `seconds` and `owner`; they default to `0` and
`null`.

## Events

When the container provides `Psr\EventDispatcher\EventDispatcherInterface`,
the repository dispatches events for reads, writes, deletes, and flushes:

- `CacheHit`, `CacheMissed`, `RetrievingKey`, `RetrievingManyKeys`
- `WritingKey`, `WritingManyKeys`, `KeyWritten`, `KeyWriteFailed`
- `ForgettingKey`, `KeyForgotten`, `KeyForgetFailed`
- `CacheFlushing`, `CacheFlushed`

Each event includes the store name. Single-key events also include the key,
write events expose the value and TTL where applicable, and bulk events expose
their keys; `WritingManyKeys` also exposes the values. Bulk reads additionally
dispatch `CacheHit` or `CacheMissed` for each returned key.

## Reference

The API is inspired by Laravel Cache, but behavior should be verified against
this component's contracts and implementation.
