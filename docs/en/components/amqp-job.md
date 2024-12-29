# Amqp Job

## Introduction

`friendsofhyperf/amqp-job` is an asynchronous task component based on the `hyperf/amqp` component. It supports dispatching tasks to an AMQP service, which are then consumed by consumers. By encapsulating the `hyperf/amqp` component, it provides a more convenient way to dispatch and consume tasks.

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

### Registering Consumers [Optional]

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