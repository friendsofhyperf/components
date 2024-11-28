# Ipc Broadcaster

[![Latest Version](https://img.shields.io/packagist/v/friendsofhyperf/ipc-broadcaster.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/ipc-broadcaster)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/ipc-broadcaster.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/ipc-broadcaster)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/ipc-broadcaster)](https://github.com/friendsofhyperf/ipc-broadcaster)

Ipc Broadcaster component for Hyperf.

## Installation

- Installation

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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
