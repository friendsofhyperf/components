# 原子锁

Hyperf 原子锁组件。[English Document](README.md)

## 安装

- 安装依赖

```bash
composer require friendsofhyperf/lock
```

- 发布配置

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

## 使用

你可以使用 `lock()` 方法来创建和管理锁：

```php
$lock = lock($name = 'foo', $seconds = 10, $owner = null);

if ($lock->get()) {
    // 获取锁定10秒...

    $lock->release();
}
```

`get` 方法也可以接收一个闭包。在闭包执行之后，将会自动释放锁：

```php
lock('foo')->get(function () {
    // 获取无限期锁并自动释放...
});
```

如果你在请求时锁无法使用，你可以控制等待指定的秒数。如果在指定的时间限制内无法获取锁，则会抛出 `FriendsOfHyperf\Lock\Exception\LockTimeoutException`

```php
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

$lock = lock('foo', 10);

try {
    $lock->block(5);

    // 等待最多5秒后获取的锁...
} catch (LockTimeoutException $e) {
    // 无法获取锁...
} finally {
    optional($lock)->release();
}

lock('foo', 10)->block(5, function () {
    // 等待最多5秒后获取的锁...
});
```

注解方式

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
            // 获取无限期锁并自动释放...
        });
    }
}
```
