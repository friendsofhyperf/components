# Confd

Hyperf's configuration management component.

## Installation

```shell
composer require friendsofhyperf/confd
composer require friendsofhyperf/etcd
# or
composer require friendsofhyperf/nacos
```

## Commands

Fetch configurations from `etcd/nacos` and update `.env`.

```shell
php bin/hyperf.php confd:env
```

## Defining Listeners

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

## Supported

- [x] Etcd
- [x] Nacos
- [ ] Consul