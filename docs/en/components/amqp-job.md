# Amqp Job

## Introduction

`friendsofhyperf/amqp-job` is an asynchronous job component based on the `hyperf/amqp` component. It supports dispatching jobs to an AMQP service and then consuming the jobs through consumers.
It encapsulates the `hyperf/amqp` component and provides a more convenient way to dispatch and consume jobs.

## Installation

```shell
composer require friendsofhyperf/amqp-job
```

## Usage

### Dispatch Job

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

### Register Consumer [Optional]

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
class MyConsumer extends JobConsumer
{
}
