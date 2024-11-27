# Trigger

## 安装

- 安装

```shell
composer require friendsofhyperf/trigger
```

- 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/trigger
```

## 添加监听器

```php
// config/autoload/listeners.php

return [
    FriendsOfHyperf\Trigger\Listener\BindTriggerProcessesListener::class => PHP_INT_MAX,
];
```

## 定义触发器

```php
namespace App\Trigger;

use FriendsOfHyperf\Trigger\Annotation\Trigger;
use FriendsOfHyperf\Trigger\Trigger\AbstractTrigger;
use MySQLReplication\Event\DTO\EventDTO;

#[Trigger(table:"table", events:"*", connection:"default")]
class FooTrigger extends AbstractTrigger
{
    public function onWrite(array $new)
    {
        var_dump($new);
    }

    public function onUpdate(array $old, array $new)
    {
        var_dump($old, $new);
    }

    public function onDelete(array $old)
    {
        var_dump($old);
    }
}
```

## 定义订阅者

```php
namespace App\Subscriber;

use FriendsOfHyperf\Trigger\Annotation\Subscriber;
use FriendsOfHyperf\Trigger\Subscriber\AbstractSubscriber;
use MySQLReplication\Event\DTO\EventDTO;

#[Subscriber(connection:"default")]
class BarSubscriber extends AbstractSubscriber
{
    protected function allEvents(EventDTO $event): void
    {
        // 一些代码
    }
}
```
