# Confd

Hyperf 的配置管理组件。

## 安装

```shell
composer require friendsofhyperf/confd
composer require friendsofhyperf/etcd
# or
composer require friendsofhyperf/nacos
```

## 命令

从 `etcd/nacos` 获取配置并更新 `.env`。

```shell
php bin/hyperf.php confd:env
```

## 定义监听器

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

## 支持驱动

- [x] Etcd
- [x] Nacos
- [ ] Consul
