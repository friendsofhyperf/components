# Amqp Job

## 简介

`friendsofhyperf/amqp-job` 是一个基于 `hyperf/amqp` 组件实现的异步任务组件，支持将任务分发到 AMQP 服务，然后通过消费者消费任务。
封装了 `hyperf/amqp` 组件，提供了更加便捷的任务分发和消费方式。

## 安装

```shell
composer require friendsofhyperf/amqp-job
```

## 用法

### 分发任务

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

### 注册消费者[可选]

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
