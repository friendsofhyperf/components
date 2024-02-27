# confd

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/confd)](https://packagist.org/packages/friendsofhyperf/confd)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/confd)](https://packagist.org/packages/friendsofhyperf/confd)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/confd)](https://github.com/friendsofhyperf/confd)

The confd component for Hyperf.

## Requirements

- PHP >= 8.0
- Hyperf >= 3.0

## Installation

```shell
composer require friendsofhyperf/confd
composer require friendsofhyperf/etcd
# or
composer require friendsofhyperf/nacos
```

## Command

Fetch configs from etcd/nacos and upgrade `.env`.

```shell
php bin/hyperf.php confd:env
```

## Listener

```php
<?php

namespace App\Listener;

use FriendsOfHyperf\Confd\Event\ConfigChanged;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener()]
class ConfigChangedListener implements ListenerInterface
{
    public function __construct(private StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            ConfigChanged::class,
        ];
    }

    public function process(object $event): void
    {
        $this->logger->warning('[confd] ConfdChanged');
        // do something
    }
}
```

## Support

- [x] Etcd
- [x] Nacos
- [ ] Consul

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
