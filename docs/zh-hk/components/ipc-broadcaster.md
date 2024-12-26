# Ipc Broadcaster

Hyperf 進程通信廣播組件。

## 安裝

```shell
composer require friendsofhyperf/ipc-broadcaster
```

## 使用

- 閉包

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

- 類

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
