# Support

The Support component provides shared utilities used across FriendsOfHyperf packages, including a
fluent dispatch API, serializable closure jobs, and retry backoff strategies.

## Installation

```shell
composer require friendsofhyperf/support
```

AMQP, Kafka, and async queue integrations are optional. Install the corresponding Hyperf package
before dispatching that message type.

## Fluent Dispatch

The `dispatch()` helper selects a pending dispatcher from the value type:

- `Closure` creates a `CallQueuedClosure` and uses the async queue dispatcher.
- `ProducerMessageInterface` uses the AMQP producer dispatcher.
- `ProduceMessage` uses the Kafka producer dispatcher.
- Other values are rejected with `InvalidArgumentException`.

### Async Queue

```php
use function FriendsOfHyperf\Support\dispatch;

dispatch(function (UserService $users) {
    $users->synchronize();
})
    ->onPool('default')
    ->delay(30)
    ->setMaxAttempts(3);
```

The closure supports dependency injection. The pending object dispatches when it is destroyed.

### AMQP and Kafka

```php
dispatch($amqpMessage)
    ->onPool('default')
    ->setExchange('events')
    ->setRoutingKey('user.updated')
    ->setTimeout(5)
    ->setConfirm(true);

dispatch($kafkaMessage)->onPool('default');
```

### Conditional Configuration

All pending dispatchers support `when()` and `unless()`:

```php
dispatch($job)
    ->when($highPriority, fn ($pending) => $pending->onPool('high-priority'))
    ->unless($canRunNow, fn ($pending) => $pending->delay(60));
```

## Closure Jobs

Create a serializable async queue job directly when you need to pass it to another API:

```php
use FriendsOfHyperf\Support\CallQueuedClosure;

$job = CallQueuedClosure::create(function () {
    return 'completed';
});
$job->setMaxAttempts(3);
```

## Backoff Strategies

Backoff implementations return the next delay in milliseconds and expose `next()`, `reset()`,
`getAttempt()`, and `sleep()`.

### Array Backoff

```php
use FriendsOfHyperf\Support\Backoff\ArrayBackoff;

$custom = new ArrayBackoff([100, 500, 1000, 2000]);
$short = ArrayBackoff::fromPattern('short');
$fromString = ArrayBackoff::fromString('100, 500, 1000');
```

Set the constructor's second argument to `false` when the strategy should return `0` after the
array is exhausted instead of repeating the last value.

### Available Strategies

- `FixedBackoff`: constant delay.
- `LinearBackoff`: delay grows by a fixed step.
- `ExponentialBackoff`: exponential growth with optional jitter.
- `FibonacciBackoff`: Fibonacci sequence delays.
- `PoissonBackoff`: delays based on a Poisson distribution.
- `DecorrelatedJitterBackoff`: decorrelated jitter for distributed retries.
- `ArrayBackoff`: caller-defined delay sequence.

## Other Utilities

The package also contains `retry()` and `once()` helpers, plus `ConfigurationUrlParser`, `Env`,
`HtmlString`, `Number`, `Once`, `Sleep`, `Timebox`, `UuidContainer`, `RedisCommand`, pipeline
helpers, and reusable traits. Review the classes under `FriendsOfHyperf\Support` before adding a
duplicate utility to another component.
