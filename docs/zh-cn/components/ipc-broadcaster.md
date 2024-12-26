# Ipc Broadcaster

Ipc Broadcaster component for Hyperf.

## 安装

```shell
composer require friendsofhyperf/ipc-broadcaster
```

## 使用

- 闭包

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

- 类

```php
namespace App\Broadcasting;

class FooMessage extends IpcMessage
{
    public function __construct(private string $foo)
    {
        //
    }

    public function handle(): void
    {
        echo $this->foo;
    }
}

use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(new FooMessage('bar'));

```
