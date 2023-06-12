# Trigger

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/trigger/version.png)](https://packagist.org/packages/friendsofhyperf/trigger)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/trigger/d/total.png)](https://packagist.org/packages/friendsofhyperf/trigger)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/trigger)](https://github.com/friendsofhyperf/trigger)

MySQL trigger component for Hyperf, Based on a great work of creators：[moln/php-mysql-replication](https://github.com/moln/php-mysql-replication)

## Installation

- Request

```bash
composer require friendsofhyperf/trigger
```

- Publish

```bash
php bin/hyperf.php vendor:publish friendsofhyperf/trigger
```

## Define a trigger

```php
namespace App\Trigger;

use FriendsOfHyperf\Trigger\Annotation\Trigger;
use FriendsOfHyperf\Trigger\Trigger\AbstractTrigger;
use MySQLReplication\Event\DTO\EventDTO;

#[Trigger(table:"table", on:"*", connection:"default")]
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

## Define a subscriber

```php
namespace App\Subscriber;

use FriendsOfHyperf\Trigger\Annotation\Subscriber;
use FriendsOfHyperf\Trigger\Subscriber\AbstractEventSubscriber;
use MySQLReplication\Event\DTO\EventDTO;

#[Subscriber(connection:"default")]
class BarSubscriber extends AbstractEventSubscriber
{
    protected function allEvents(EventDTO $event): void
    {
        // some code
    }
}
```

## Sponsor

If you like them, Buy me a cup of coffee. [ [Alipay](https://hdj.me/images/alipay.jpg) | [WePay](https://hdj.me/images/wechat-pay.jpg) ]
