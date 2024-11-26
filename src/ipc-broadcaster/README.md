# TcpSender

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/ipc-broadcaster/version.png)](https://packagist.org/packages/friendsofhyperf/ipc-broadcaster)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/ipc-broadcaster/d/total.png)](https://packagist.org/packages/friendsofhyperf/ipc-broadcaster)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/ipc-broadcaster)](https://github.com/friendsofhyperf/ipc-broadcaster)

Another TcpSender component for Hyperf.

## Installation

- Installation

```shell
composer require friendsofhyperf/ipc-broadcaster
```

## Usage

```php
use function FriendsOfHyperf\IpcBroadcaster\broadcast;

broadcast(function () {
    echo 'Hello world';
});
```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
