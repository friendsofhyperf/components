# Amqp Job

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/amqp-job)](https://packagist.org/packages/friendsofhyperf/amqp-job)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/amqp-job)](https://packagist.org/packages/friendsofhyperf/amqp-job)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/amqp-job)](https://github.com/friendsofhyperf/amqp-job)

The amqp job component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/amqp-job
```

## Usage

### Dispatch

```php
use FriendsOfHyperf\AmqpJob\Job;
use FriendsOfHyperf\AmqpJob\Annotations\AmqpJob;

use function FriendsOfHyperf\AmqpJob\dispatch;

#[AmqpJob(
    exchange: 'hyperf.exchange',
    routingKey: 'hyperf.routing.key',
    pool: 'default',
    queue: 'hyperf.queue',
)]
class FooJob extends Job
{
    public function handle()
    {
        var_dump('foo');
    }
}

dispatch(new FooJob());

```

### Register Consumer[Optional]

```php

namespace App\Amqp\Consumer;

use FriendsOfHyperf\AmqpJob\JobConsumer;
use Hyperf\Amqp\Annotation\Consumer;

#[Consumer(
    exchange: 'hyperf.exchange',
    routingKey: 'hyperf.routing.key',
    queue: 'hyperf.queue',
    name: 'MyConsumer',
    nums: 4

)]
class MyConsumer extends \FriendsOfHyperf\AmqpJob\JobConsumer
{
    //
}

```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
