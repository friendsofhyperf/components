# Ipc Broadcaster

Hyperf Inter-Process Communication Broadcast Component.

## Installation

```shell
composer require friendsofhyperf/ipc-broadcaster
```

## Usage

- Closure

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

- Class

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