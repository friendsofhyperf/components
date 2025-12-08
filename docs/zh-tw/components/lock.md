# Lock

Hyperf 原子鎖元件。

## 安裝

- 安裝

```shell
composer require friendsofhyperf/lock
```

- 釋出配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/lock -i config
```

## 使用

你可以使用 `lock()` 方法來建立和管理鎖：

```php
$lock = lock($name = 'foo', $seconds = 10, $owner = null);

if ($lock->get()) {
    // 獲取鎖定10秒...

    $lock->release();
}
```

`get` 方法也可以接收一個閉包。在閉包執行之後，將會自動釋放鎖：

```php
lock('foo')->get(function () {
    // 獲取無限期鎖並自動釋放...
});
```

如果你在請求時鎖無法使用，你可以控制等待指定的秒數。如果在指定的時間限制內無法獲取鎖，則會丟擲 `FriendsOfHyperf\Lock\Exception\LockTimeoutException`

```php
use FriendsOfHyperf\Lock\Exception\LockTimeoutException;

$lock = lock('foo', 10);

try {
    $lock->block(5);

    // 等待最多5秒後獲取的鎖...
} catch (LockTimeoutException $e) {
    // 無法獲取鎖...
} finally {
    $lock->release();
}

lock('foo', 10)->block(5, function () {
    // 等待最多5秒後獲取的鎖...
});
```

註解方式

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
            // 獲取無限期鎖並自動釋放...
        });
    }
}
```
