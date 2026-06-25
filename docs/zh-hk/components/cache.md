# Cache

## 簡介

`friendsofhyperf/cache` 使用 Laravel 風格的倉庫 API 封裝 `hyperf/cache`
提供的驅動。它支持 PSR-16 操作、命名存儲、門面訪問、緩存事件、宏以及
stale-while-revalidate 緩存。

## 安裝

```shell
composer require friendsofhyperf/cache
```

該組件要求 Hyperf 3.2。其 `ConfigProvider` 會在容器中註冊
`FriendsOfHyperf\Cache\Contract\Factory` 和
`FriendsOfHyperf\Cache\Contract\Repository`。

## 配置

該組件不會發布獨立的配置文件。請通過 `hyperf/cache` 配置驅動和命名存儲；
`CacheManager::store($name)` 會將名稱傳給 Hyperf 的緩存管理器。默認倉庫解析
`default` 存儲。

## 獲取倉庫

### 依賴注入

注入 `Contract\Repository` 以使用默認存儲：

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

需要命名存儲時注入 `Contract\Factory`：

```php
use FriendsOfHyperf\Cache\Contract\Factory;

$cache = $factory->store('redis');
```

### 門面

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::remember('users', 60, function () {
    return [];
});

$users = Cache::store('redis')->get('users');
```

`Cache::driver($name)` 是 `Cache::store($name)` 的別名。
`Cache::resolve($name)` 會創建新倉庫，而不是返回管理器中緩存的倉庫實例。

## 核心操作

倉庫實現了 `Psr\SimpleCache\CacheInterface`，因此可使用 `get()`、`set()`、
`delete()`、`clear()`、`getMultiple()`、`setMultiple()`、`deleteMultiple()` 和
`has()`。此外還提供以下擴展：

| 方法 | 行為 |
| --- | --- |
| `get($key, $default = null)` | 獲取單個項目；未命中時會執行可調用的默認值。傳入數組會轉交給 `many()`。 |
| `put($key, $value, $ttl = null)` | 存儲單個項目；`null` 表示永久存儲，非正數 TTL 會刪除該鍵。傳入關聯數組會轉交給 `putMany()`，並將第二個參數用作其 TTL。 |
| `putMany($values, $ttl = null)` | 存儲多個項目；非正數 TTL 會刪除對應鍵。 |
| `forever($key, $value)` | 不設置 TTL 地存儲單個項目。 |
| `add($key, $value, $ttl = null)` | 僅當 `get($key)` 返回 `null` 時存儲項目。 |
| `many($keys)` | 獲取多個鍵；關聯數組輸入可為每個鍵提供默認值。 |
| `pull($key, $default = null)` | 獲取項目後將其刪除。 |
| `remember($key, $ttl, Closure $callback)` | 返回緩存值，或按 TTL 存儲回調結果。 |
| `rememberForever($key, Closure $callback)` / `sear(...)` | 返回緩存值，或永久存儲回調結果。 |
| `increment($key, $value = 1)` / `decrement(...)` | 讀取、調整並以無 TTL 方式存儲整數值。 |
| `flush()` | `clear()` 的別名。 |
| `missing($key)` | `has($key)` 的反向判斷。 |
| `getDriver()` / `getStore()` | 返回底層 Hyperf `DriverInterface`。 |

擴展倉庫方法接受的 TTL 可以是秒數、`DateInterval` 或 `DateTimeInterface`。

::: warning 行為邊界
倉庫會將已緩存的 `null` 視為未命中。`add()`、`pull()` 和遞增/遞減方法由分離的
操作實現，因此不是原子操作。雖然 `pull()` 接受 `$default` 參數，但當前實現不會
將它傳給 `get()`，鍵不存在時返回 `null`。
:::

## Stale-While-Revalidate

`flexible()` 接受包含兩個 TTL 的數組：第一個值是新鮮期，第二個值是緩存值及其
內部創建時間戳使用的存儲 TTL。

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::flexible('users', [30, 300], function () {
    return [];
}, [
    'seconds' => 10,
    'owner' => 'users-refresh',
]);
```

緩存未命中時，回調立即執行。在新鮮期內直接返回緩存值；新鮮期結束後返回舊值，
並通過延遲迴調嘗試在鎖內刷新緩存。如果其他進程已更新創建時間戳，則跳過刷新。

使用 `flexible()` 前需要安裝兩個可選依賴：

```shell
composer require friendsofhyperf/lock hyperf/coroutine
```

可選的鎖數組接受 `seconds` 和 `owner`，默認值分別為 `0` 和 `null`。

## 事件

當容器提供 `Psr\EventDispatcher\EventDispatcherInterface` 時，倉庫會為讀取、
寫入、刪除和清空操作分發事件：

- `CacheHit`、`CacheMissed`、`RetrievingKey`、`RetrievingManyKeys`
- `WritingKey`、`WritingManyKeys`、`KeyWritten`、`KeyWriteFailed`
- `ForgettingKey`、`KeyForgotten`、`KeyForgetFailed`
- `CacheFlushing`、`CacheFlushed`

每個事件都包含存儲名稱。單鍵事件還包含鍵；適用時，寫入事件會公開值和 TTL，
批量事件會公開鍵，`WritingManyKeys` 還會公開值。批量讀取還會為每個返回的鍵分發
`CacheHit` 或 `CacheMissed`。

## 參考

該 API 的設計受 Laravel Cache 啓發，但具體行為應以本組件的契約和實現為準。
