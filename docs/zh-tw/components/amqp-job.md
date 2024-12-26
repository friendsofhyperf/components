# Amqp Job

## 簡介

`friendsofhyperf/amqp-job` 是一個基於 `hyperf/amqp` 元件實現的非同步任務元件，支援將任務分發到 AMQP 服務，然後透過消費者消費任務。封裝了 `hyperf/amqp` 元件，提供了更加便捷的任務分發和消費方式。

## 安裝

```shell
composer require friendsofhyperf/amqp-job
```

## 用法

### 分發任務

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

### 註冊消費者[可選]

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
