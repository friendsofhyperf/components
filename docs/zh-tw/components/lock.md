# Lock

Hyperf 原子鎖元件。

## 安裝

```shell
composer require friendsofhyperf/lock
```

預設驅動使用 Redis。請根據使用的驅動安裝對應的可選依賴：

| 驅動 | 配置名稱 | 可選依賴 |
| --- | --- | --- |
| `RedisLock` | `default` | `hyperf/redis` |
| `FileSystemLock` | `file` | `hyperf/cache` |
| `DatabaseLock` | `database` | `hyperf/db-connection` |
| `CoroutineLock` | `co` | 無 |
| `CacheLock` | 未釋出 | `hyperf/cache` |

釋出配置檔案：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

使用資料庫驅動時，還需要釋出並執行鎖遷移：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i migrations
php bin/hyperf.php migrate
```

執行釋出的遷移前，請將其中的 `value` 列改為 `owner`。當前 `DatabaseLock` 實現讀寫的是
`owner` 列。

## 配置

釋出的 `config/autoload/lock.php` 包含 `default`、`file`、`database` 和 `co` 配置。每項配置
用於選擇驅動類，並將其中的 `constructor` 選項傳給驅動。`lock()` 和
`LockFactory::make()` 的第四個引數是配置名稱，而不是驅動類名。

所選的 `lock.<driver>` 配置不存在時，工廠會丟擲 `InvalidArgumentException`。你也可以新增
自定義配置，其驅動須實現 `FriendsOfHyperf\Lock\Driver\LockInterface`。

## 建立鎖

匯入並呼叫帶名稱空間的輔助函式：

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

不傳名稱呼叫 `lock()` 時返回 `LockFactory`，否則返回 `LockInterface` 例項。引數如下：

- `name`：鎖名稱。
- `seconds`：請求的鎖有效期秒數，預設為 `0`。
- `owner`：可選的所有者標識；省略時會隨機生成。
- `driver`：`lock` 下的配置名稱，預設為 `default`。

TTL 行為取決於驅動。特別是當 `seconds` 為 `0` 或負數時，`DatabaseLock` 會儲存一天的
過期時間。

## 鎖操作

`LockInterface` 公開 `get()`、`block()`、`release()`、`owner()`、`forceRelease()`、
`refresh()`、`isExpired()` 和 `getRemainingLifetime()`。

`get()` 只嘗試獲取一次；未傳回調時返回布林值。傳入回撥時，它會返回回撥結果，並在
`finally` 塊中釋放鎖：

```php
$result = lock('foo', 10)->get(function () {
    return 'completed';
});
```

`block()` 會重試，直到獲取鎖或達到等待時限；超時時丟擲 `LockTimeoutException`。傳入回撥
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

`release()` 只釋放當前所有者持有的鎖，`forceRelease()` 會忽略所有權。僅當驅動支援時，
`refresh($ttl)` 才能重新整理過期時間；TTL 不為正數時返回 `false`。`isExpired()` 和
`getRemainingLifetime()` 返回鎖例項跟蹤的過期狀態。

## 註解

### 屬性注入

`#[Lock]` 用於屬性，並在應用啟動時注入鎖例項。引數為 `name`、`seconds`、`owner` 和
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

`#[Blockable]` 用於方法。當 `seconds` 大於 `0` 時，它會建立鎖並最多等待 `seconds` 秒獲取
鎖，然後在方法返回或丟擲異常後自動釋放。`ttl` 是鎖有效期，`driver` 用於選擇鎖配置。
`prefix` 和 `value` 會結合方法引數格式化為鎖名稱。

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
