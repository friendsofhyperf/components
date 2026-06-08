# Cache

## 簡介

`friendsofhyperf/cache` 使用 Laravel 風格的儲存庫 API 封裝 `hyperf/cache`
提供的驅動程式。它支援 PSR-16 操作、具名儲存、Facade 存取、快取事件、巨集以及
stale-while-revalidate 快取。

## 安裝

```shell
composer require friendsofhyperf/cache
```

該元件要求 Hyperf 3.2。其 `ConfigProvider` 會在容器中註冊
`FriendsOfHyperf\Cache\Contract\Factory` 和
`FriendsOfHyperf\Cache\Contract\Repository`。

## 設定

該元件不會發布獨立的設定檔。請透過 `hyperf/cache` 設定驅動程式和具名儲存；
`CacheManager::store($name)` 會將名稱傳給 Hyperf 的快取管理器。預設儲存庫解析
`default` 儲存。

## 取得儲存庫

### 相依性注入

注入 `Contract\Repository` 以使用預設儲存：

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

需要具名儲存時注入 `Contract\Factory`：

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

`Cache::driver($name)` 是 `Cache::store($name)` 的別名。
`Cache::resolve($name)` 會建立新儲存庫，而不是傳回管理器中快取的儲存庫執行個體。

## 核心操作

儲存庫實作了 `Psr\SimpleCache\CacheInterface`，因此可使用 `get()`、`set()`、
`delete()`、`clear()`、`getMultiple()`、`setMultiple()`、`deleteMultiple()` 和
`has()`。此外還提供以下擴充：

| 方法 | 行為 |
| --- | --- |
| `get($key, $default = null)` | 取得單一項目；未命中時會執行可呼叫的預設值。傳入陣列會轉交給 `many()`。 |
| `put($key, $value, $ttl = null)` | 儲存單一項目；`null` 表示永久儲存，非正數 TTL 會刪除該鍵。傳入關聯陣列會轉交給 `putMany()`，並將第二個參數用作其 TTL。 |
| `putMany($values, $ttl = null)` | 儲存多個項目；非正數 TTL 會刪除對應鍵。 |
| `forever($key, $value)` | 不設定 TTL 地儲存單一項目。 |
| `add($key, $value, $ttl = null)` | 僅當 `get($key)` 傳回 `null` 時儲存項目。 |
| `many($keys)` | 取得多個鍵；關聯陣列輸入可為每個鍵提供預設值。 |
| `pull($key, $default = null)` | 取得項目後將其刪除。 |
| `remember($key, $ttl, Closure $callback)` | 傳回快取值，或按 TTL 儲存回呼結果。 |
| `rememberForever($key, Closure $callback)` / `sear(...)` | 傳回快取值，或永久儲存回呼結果。 |
| `increment($key, $value = 1)` / `decrement(...)` | 讀取、調整並以無 TTL 方式儲存整數值。 |
| `flush()` | `clear()` 的別名。 |
| `missing($key)` | `has($key)` 的反向判斷。 |
| `getDriver()` / `getStore()` | 傳回底層 Hyperf `DriverInterface`。 |

擴充儲存庫方法接受的 TTL 可以是秒數、`DateInterval` 或 `DateTimeInterface`。

::: warning 行為邊界
儲存庫會將已快取的 `null` 視為未命中。`add()`、`pull()` 和遞增/遞減方法由分離的
操作實作，因此不具原子性。雖然 `pull()` 接受 `$default` 參數，但目前實作不會
將它傳給 `get()`，鍵不存在時傳回 `null`。
:::

## Stale-While-Revalidate

`flexible()` 接受包含兩個 TTL 的陣列：第一個值是新鮮期，第二個值是快取值及其
內部建立時間戳使用的儲存 TTL。

```php
use FriendsOfHyperf\Cache\Facade\Cache;

$users = Cache::flexible('users', [30, 300], function () {
    return [];
}, [
    'seconds' => 10,
    'owner' => 'users-refresh',
]);
```

快取未命中時，回呼立即執行。在新鮮期內直接傳回快取值；新鮮期結束後傳回舊值，
並透過延遲回呼嘗試在鎖內重新整理快取。如果其他處理程序已更新建立時間戳，則
跳過重新整理。

使用 `flexible()` 前需要安裝兩個選用相依套件：

```shell
composer require friendsofhyperf/lock hyperf/coroutine
```

選用的鎖陣列接受 `seconds` 和 `owner`，預設值分別為 `0` 和 `null`。

## 事件

當容器提供 `Psr\EventDispatcher\EventDispatcherInterface` 時，儲存庫會為讀取、
寫入、刪除和清空操作分派事件：

- `CacheHit`、`CacheMissed`、`RetrievingKey`、`RetrievingManyKeys`
- `WritingKey`、`WritingManyKeys`、`KeyWritten`、`KeyWriteFailed`
- `ForgettingKey`、`KeyForgotten`、`KeyForgetFailed`
- `CacheFlushing`、`CacheFlushed`

每個事件都包含儲存名稱。單鍵事件還包含鍵；適用時，寫入事件會公開值和 TTL，
批次事件會公開鍵，`WritingManyKeys` 還會公開值。批次讀取還會為每個傳回的鍵分派
`CacheHit` 或 `CacheMissed`。

## 參考

該 API 的設計受 Laravel Cache 啟發，但具體行為應以本元件的契約和實作為準。
