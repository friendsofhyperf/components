# Amqp Job

## Introduction

`friendsofhyperf/amqp-job` is an asynchronous task component built on the `hyperf/amqp` package. It supports distributing tasks to the AMQP service and consuming these tasks through consumers. It encapsulates the `hyperf/amqp` package and provides a more convenient way to distribute and consume tasks.

## Installation

```shell
composer require friendsofhyperf/amqp-job
```

## Usage

### Dispatching Tasks

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

### Registering a Consumer [Optional]

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
    //
}
```