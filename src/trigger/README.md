# Trigger

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/trigger)](https://packagist.org/packages/friendsofhyperf/trigger)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/trigger)](https://packagist.org/packages/friendsofhyperf/trigger)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/trigger)](https://github.com/friendsofhyperf/trigger)

MySQL trigger component for Hyperf, Based on a great work of creators：[moln/php-mysql-replication](https://github.com/moln/php-mysql-replication)

## Installation

- Request

```shell
composer require friendsofhyperf/trigger
```

- Publish

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/trigger
```

## Add listener

```php
// config/autoload/listeners.php

return [
    FriendsOfHyperf\Trigger\Listener\BindTriggerProcessesListener::class => PHP_INT_MAX,
];
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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
