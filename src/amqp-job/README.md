# Amqp Job

## Introduction

`friendsofhyperf/amqp-job` dispatches PHP job objects through `hyperf/amqp`. A job
declares its AMQP binding with the `AmqpJob` attribute, and the component automatically
registers a consumer for each enabled attributed job when the server starts.

The component uses PHP serialization by default, so only dispatch trusted job objects and
keep their classes available to consumers.

## Installation

```shell
composer require friendsofhyperf/amqp-job
```

The package's `ConfigProvider` is discovered by Hyperf. It registers the consumer listener
and binds the retry-attempt store to Redis and the message packer to
`PhpSerializerPacker`. Configure `hyperf/amqp` and its selected AMQP pool before dispatching
jobs. Setting `amqp.enable` to `false` skips all automatic job-consumer registration.

## Define And Dispatch A Job

```php
use FriendsOfHyperf\AmqpJob\Annotation\AmqpJob;
use FriendsOfHyperf\AmqpJob\Job;
use FriendsOfHyperf\AmqpJob\JobMessage;
use Hyperf\Amqp\Result;
use Hyperf\Amqp\Producer;

#[AmqpJob(
    exchange: 'hyperf.exchange',
    routingKey: 'hyperf.routing.key',
    pool: 'default',
    queue: 'hyperf.queue',
    maxAttempts: 3,
    confirm: true,
    nums: 2,
)]
class FooJob extends Job
{
    public function __construct(private readonly int $id)
    {
    }

    public function handle(): Result
    {
        // Process $this->id.
        return Result::ACK;
    }
}

function dispatchFoo(Producer $producer, int $id): bool
{
    $message = (new JobMessage(new FooJob($id)))
        ->setExchange('hyperf.exchange')
        ->setRoutingKey('hyperf.routing.key')
        ->setPoolName('default');

    return $producer->produce($message, confirm: true, timeout: 5);
}
```

`dispatch(JobInterface $payload, ?bool $confirm = null, ?int $timeout = null): bool`
is a convenience function that creates and publishes a `JobMessage`. It assigns a unique
job ID when the job does not already have one. The optional `confirm` and `timeout`
arguments override the job's values for that dispatch.

Currently, `dispatch()` does not copy the job's exchange, routing key, or pool into the
`JobMessage`. The resulting producer message therefore uses an empty exchange, an empty
routing key, and Hyperf's `default` AMQP pool. To publish to the binding declared by
`AmqpJob`, create a `JobMessage`, set its destination as shown above, and pass it to
`Hyperf\Amqp\Producer`.

## Attribute Options

| Option | Type | Default | Behavior |
| --- | --- | --- | --- |
| `exchange` | `string` | required | Exchange used by the automatic consumer and returned by `Job::getExchange()`. |
| `routingKey` | `string` | required | Routing key used by the automatic consumer and returned by `Job::getRoutingKey()`. |
| `pool` | `?string` | `null` | Automatic consumer pool and value returned by `Job::getPoolName()`. |
| `queue` | `?string` | `null` | Consumer queue; left unset when omitted. |
| `maxAttempts` | `int` | `0` | Maximum failed handling attempts; `0` disables retries. |
| `maxConsumption` | `int` | `0` | Maximum messages consumed before the consumer exits. |
| `confirm` | `bool` | `false` | Value returned by `Job::getConfirm()` and used by `dispatch()`. |
| `enable` | `bool` | `true` | Controls automatic consumer registration. |
| `nums` | `int` | `1` | Number of consumer processes. |
| `name` | `?string` | `null` | Consumer process name. |

If a job does not use the attribute, its getters fall back to exchange `hyperf`, routing
key `hyperf.job`, no explicit pool, publisher confirmation disabled, a publish timeout of
5 seconds, and no retries. A subclass may provide protected properties such as
`$exchange`, `$routingKey`, `$poolName`, `$confirm`, `$timeout`, or `$maxAttempts` to
override those getter values. As noted above, `dispatch()` only consumes the confirm and
timeout getters.

## Consumption And Retries

`JobConsumer` acknowledges a job when `handle()` returns nothing or an unrecognized
string. Returning a `Hyperf\Amqp\Result` or its string value controls the result directly.

When `handle()` throws, the consumer logs the exception when a compatible logger is
available. With `maxAttempts > 0`, the Redis-backed attempt counter requeues the job until
the configured total attempt count is reached. It then calls `fail(Throwable $e)` and
drops the message. The default `fail()` method does nothing and can be overridden:

```php
use Throwable;

public function fail(Throwable $e): void
{
    // Report or persist the terminal failure.
}
```

The default Redis attempt keys use the `hyperf:amqp-job:attempts:` prefix and expire after
86,400 seconds. Redis is therefore required when retries are enabled. Bind
`FriendsOfHyperf\AmqpJob\Contract\Attempt` or `FriendsOfHyperf\AmqpJob\Contract\Packer` to
replace the default implementations. For exception logging, the consumer first resolves
`FriendsOfHyperf\AmqpJob\Contract\LoggerInterface`, then
`Hyperf\Contract\StdoutLoggerInterface`; logging is skipped when neither is available.

## Register A Custom Consumer

Automatic consumers are normally sufficient. To consume compatible serialized jobs with
a manually declared consumer, extend `JobConsumer` and use Hyperf's `Consumer` attribute:

```php
namespace App\Amqp\Consumer;

use FriendsOfHyperf\AmqpJob\JobConsumer;
use Hyperf\Amqp\Annotation\Consumer;

#[Consumer(
    exchange: 'hyperf.exchange',
    routingKey: 'hyperf.routing.key',
    queue: 'hyperf.queue',
    name: 'MyConsumer',
    nums: 4,
)]
class MyConsumer extends JobConsumer
{
}
```
