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

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
