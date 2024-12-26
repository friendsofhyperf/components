# Confd

Hyperf 的配置管理組件。

## 安裝

```shell
composer require friendsofhyperf/confd
composer require friendsofhyperf/etcd
# or
composer require friendsofhyperf/nacos
```

## 命令

從 `etcd/nacos` 獲取配置並更新 `.env`。

```shell
php bin/hyperf.php confd:env
```

## 定義監聽器

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

## 支持

- [x] Etcd
- [x] Nacos
- [ ] Consul
