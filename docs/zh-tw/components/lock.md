# Lock

Hyperf 原子鎖元件。

## 安裝

```shell
composer require friendsofhyperf/lock
```

預設驅動使用 Redis。請依照使用的驅動安裝對應的選用依賴：

| 驅動 | 設定名稱 | 選用依賴 |
| --- | --- | --- |
| `RedisLock` | `default` | `hyperf/redis` |
| `FileSystemLock` | `file` | `hyperf/cache` |
| `DatabaseLock` | `database` | `hyperf/db-connection` |
| `CoroutineLock` | `co` | 無 |
| `CacheLock` | 未發布 | `hyperf/cache` |

發布設定檔：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

使用資料庫驅動時，還需要發布並執行鎖遷移：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i migrations
php bin/hyperf.php migrate
```

執行發布的遷移前，請將其中的 `value` 欄位改為 `owner`。目前 `DatabaseLock` 實作讀寫的是
`owner` 欄位。

## 設定

發布的 `config/autoload/lock.php` 包含 `default`、`file`、`database` 和 `co` 設定。每項設定
用於選擇驅動類別，並將其中的 `constructor` 選項傳給驅動。`lock()` 和
`LockFactory::make()` 的第四個參數是設定名稱，而不是驅動類別名稱。

所選的 `lock.<driver>` 設定不存在時，工廠會擲出 `InvalidArgumentException`。你也可以新增
自訂設定，其驅動須實作 `FriendsOfHyperf\Lock\Driver\LockInterface`。

## 建立鎖

匯入並呼叫帶命名空間的輔助函式：

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

不傳名稱呼叫 `lock()` 時回傳 `LockFactory`，否則回傳 `LockInterface` 實例。參數如下：

- `name`：鎖名稱。
- `seconds`：請求的鎖有效期秒數，預設為 `0`。
- `owner`：選用的擁有者識別；省略時會隨機產生。
- `driver`：`lock` 下的設定名稱，預設為 `default`。

TTL 行為取決於驅動。特別是當 `seconds` 為 `0` 或負數時，`DatabaseLock` 會儲存一天的
過期時間。

## 鎖操作

`LockInterface` 公開 `get()`、`block()`、`release()`、`owner()`、`forceRelease()`、
`refresh()`、`isExpired()` 和 `getRemainingLifetime()`。

`get()` 只嘗試取得一次；未傳回呼時回傳布林值。傳入回呼時，它會回傳回呼結果，並在
`finally` 區塊中釋放鎖：

```php
$result = lock('foo', 10)->get(function () {
    return 'completed';
});
```

`block()` 會重試，直到取得鎖或達到等待時限；逾時時擲出 `LockTimeoutException`。傳入回呼
時也會自動釋放鎖：

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

`release()` 只釋放目前擁有者持有的鎖，`forceRelease()` 會忽略擁有權。僅當驅動支援時，
`refresh($ttl)` 才能重新整理過期時間；TTL 不為正數時回傳 `false`。`isExpired()` 和
`getRemainingLifetime()` 回傳鎖實例追蹤的過期狀態。

## 註解

### 屬性注入

`#[Lock]` 用於屬性，並在應用程式啟動時注入鎖實例。參數為 `name`、`seconds`、`owner` 和
`driver`。

```php
use FriendsOfHyperf\Lock\Annotation\Lock;
use FriendsOfHyperf\Lock\Driver\LockInterface;

class Foo
{
    #[Lock(name: 'foo', seconds: 10, driver: 'default')]
    protected LockInterface $lock;
}
```

### 阻塞方法

`#[Blockable]` 用於方法。當 `seconds` 大於 `0` 時，它會建立鎖並最多等待 `seconds` 秒取得
鎖，然後在方法回傳或擲出例外後自動釋放。`ttl` 是鎖有效期，`driver` 用於選擇鎖設定。
`prefix` 和 `value` 會結合方法參數格式化為鎖名稱。

`Blockable` 使用 `Hyperf\Cache\Helper\StringHelper`，因此使用此註解前需安裝
`hyperf/cache`。

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
