# Cache

[English](README.md)

## 简介

`friendsofhyperf/cache` 使用 Laravel 风格的仓库 API 封装 `hyperf/cache`
提供的驱动。它支持 PSR-16 操作、命名存储、门面访问、缓存事件、宏以及
stale-while-revalidate 缓存。

## 安装

```shell
composer require friendsofhyperf/cache
```

该组件要求 Hyperf 3.2。其 `ConfigProvider` 会在容器中注册
`FriendsOfHyperf\Cache\Contract\Factory` 和
`FriendsOfHyperf\Cache\Contract\Repository`。

## 配置

该组件不会发布独立的配置文件。请通过 `hyperf/cache` 配置驱动和命名存储；
`CacheManager::store($name)` 会将名称传给 Hyperf 的缓存管理器。默认仓库解析
`default` 存储。

## 获取仓库

### 依赖注入

注入 `Contract\Repository` 以使用默认存储：

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

需要命名存储时注入 `Contract\Factory`：

```php
use FriendsOfHyperf\Cache\Contract\Factory;

$cache = $factory->store('redis');
```

### 门面

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::remember('users', 60, function () {
    return [];
});

$users = Cache::store('redis')->get('users');
```

`Cache::driver($name)` 是 `Cache::store($name)` 的别名。
`Cache::resolve($name)` 会创建新仓库，而不是返回管理器中缓存的仓库实例。

## 核心操作

仓库实现了 `Psr\SimpleCache\CacheInterface`，因此可使用 `get()`、`set()`、
`delete()`、`clear()`、`getMultiple()`、`setMultiple()`、`deleteMultiple()` 和
`has()`。此外还提供以下扩展：

| 方法 | 行为 |
| --- | --- |
| `get($key, $default = null)` | 获取单个项目；未命中时会执行可调用的默认值。传入数组会转交给 `many()`。 |
| `put($key, $value, $ttl = null)` | 存储单个项目；`null` 表示永久存储，非正数 TTL 会删除该键。传入关联数组会转交给 `putMany()`，并将第二个参数用作其 TTL。 |
| `putMany($values, $ttl = null)` | 存储多个项目；非正数 TTL 会删除对应键。 |
| `forever($key, $value)` | 不设置 TTL 地存储单个项目。 |
| `add($key, $value, $ttl = null)` | 仅当 `get($key)` 返回 `null` 时存储项目。 |
| `many($keys)` | 获取多个键；关联数组输入可为每个键提供默认值。 |
| `pull($key, $default = null)` | 获取项目后将其删除。 |
| `remember($key, $ttl, Closure $callback)` | 返回缓存值，或按 TTL 存储回调结果。 |
| `rememberForever($key, Closure $callback)` / `sear(...)` | 返回缓存值，或永久存储回调结果。 |
| `increment($key, $value = 1)` / `decrement(...)` | 读取、调整并以无 TTL 方式存储整数值。 |
| `flush()` | `clear()` 的别名。 |
| `missing($key)` | `has($key)` 的反向判断。 |
| `getDriver()` / `getStore()` | 返回底层 Hyperf `DriverInterface`。 |

扩展仓库方法接受的 TTL 可以是秒数、`DateInterval` 或 `DateTimeInterface`。

::: warning 行为边界
仓库会将已缓存的 `null` 视为未命中。`add()`、`pull()` 和递增/递减方法由分离的
操作实现，因此不是原子操作。虽然 `pull()` 接受 `$default` 参数，但当前实现不会
将它传给 `get()`，键不存在时返回 `null`。
:::

## Stale-While-Revalidate

`flexible()` 接受包含两个 TTL 的数组：第一个值是新鲜期，第二个值是缓存值及其
内部创建时间戳使用的存储 TTL。

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::flexible('users', [30, 300], function () {
    return [];
}, [
    'seconds' => 10,
    'owner' => 'users-refresh',
]);
```

缓存未命中时，回调立即执行。在新鲜期内直接返回缓存值；新鲜期结束后返回旧值，
并通过延迟回调尝试在锁内刷新缓存。如果其他进程已更新创建时间戳，则跳过刷新。

使用 `flexible()` 前需要安装两个可选依赖：

```shell
composer require friendsofhyperf/lock hyperf/coroutine
```

可选的锁数组接受 `seconds` 和 `owner`，默认值分别为 `0` 和 `null`。

## 事件

当容器提供 `Psr\EventDispatcher\EventDispatcherInterface` 时，仓库会为读取、
写入、删除和清空操作分发事件：

- `CacheHit`、`CacheMissed`、`RetrievingKey`、`RetrievingManyKeys`
- `WritingKey`、`WritingManyKeys`、`KeyWritten`、`KeyWriteFailed`
- `ForgettingKey`、`KeyForgotten`、`KeyForgetFailed`
- `CacheFlushing`、`CacheFlushed`

每个事件都包含存储名称。单键事件还包含键；适用时，写入事件会公开值和 TTL，
批量事件会公开键，`WritingManyKeys` 还会公开值。批量读取还会为每个返回的键分发
`CacheHit` 或 `CacheMissed`。

## 参考

该 API 的设计受 Laravel Cache 启发，但具体行为应以本组件的契约和实现为准。
