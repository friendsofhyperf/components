# Lock

Hyperf 原子锁组件。

## 安装

```shell
composer require friendsofhyperf/lock
```

默认驱动使用 Redis。请根据使用的驱动安装对应的可选依赖：

| 驱动 | 配置名称 | 可选依赖 |
| --- | --- | --- |
| `RedisLock` | `default` | `hyperf/redis` |
| `FileSystemLock` | `file` | `hyperf/cache` |
| `DatabaseLock` | `database` | `hyperf/db-connection` |
| `CoroutineLock` | `co` | 无 |
| `CacheLock` | 未发布 | `hyperf/cache` |

发布配置文件：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

使用数据库驱动时，还需要发布并执行锁迁移：

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i migrations
php bin/hyperf.php migrate
```

运行发布的迁移前，请将其中的 `value` 列改为 `owner`。当前 `DatabaseLock` 实现读写的是
`owner` 列。

## 配置

发布的 `config/autoload/lock.php` 包含 `default`、`file`、`database` 和 `co` 配置。每项配置
用于选择驱动类，并将其中的 `constructor` 选项传给驱动。`lock()` 和
`LockFactory::make()` 的第四个参数是配置名称，而不是驱动类名。

所选的 `lock.<driver>` 配置不存在时，工厂会抛出 `InvalidArgumentException`。你也可以新增
自定义配置，其驱动须实现 `FriendsOfHyperf\Lock\Driver\LockInterface`。

## 创建锁

导入并调用带命名空间的辅助函数：

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

不传名称调用 `lock()` 时返回 `LockFactory`，否则返回 `LockInterface` 实例。参数如下：

- `name`：锁名称。
- `seconds`：请求的锁有效期秒数，默认为 `0`。
- `owner`：可选的所有者标识；省略时会随机生成。
- `driver`：`lock` 下的配置名称，默认为 `default`。

TTL 行为取决于驱动。特别是当 `seconds` 为 `0` 或负数时，`DatabaseLock` 会存储一天的
过期时间。

## 锁操作

`LockInterface` 公开 `get()`、`block()`、`release()`、`owner()`、`forceRelease()`、
`refresh()`、`isExpired()` 和 `getRemainingLifetime()`。

`get()` 只尝试获取一次；未传回调时返回布尔值。传入回调时，它会返回回调结果，并在
`finally` 块中释放锁：

```php
$result = lock('foo', 10)->get(function () {
    return 'completed';
});
```

`block()` 会重试，直到获取锁或达到等待时限；超时时抛出 `LockTimeoutException`。传入回调
时也会自动释放锁：

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

`release()` 只释放当前所有者持有的锁，`forceRelease()` 会忽略所有权。仅当驱动支持时，
`refresh($ttl)` 才能刷新过期时间；TTL 不为正数时返回 `false`。`isExpired()` 和
`getRemainingLifetime()` 返回锁实例跟踪的过期状态。

## 注解

### 属性注入

`#[Lock]` 用于属性，并在应用启动时注入锁实例。参数为 `name`、`seconds`、`owner` 和
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

`#[Blockable]` 用于方法。当 `seconds` 大于 `0` 时，它会创建锁并最多等待 `seconds` 秒获取
锁，然后在方法返回或抛出异常后自动释放。`ttl` 是锁有效期，`driver` 用于选择锁配置。
`prefix` 和 `value` 会结合方法参数格式化为锁名称。

`Blockable` 使用 `Hyperf\Cache\Helper\StringHelper`，因此使用此注解前需安装
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
