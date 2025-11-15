# Support

[![Latest Version](https://img.shields.io/packagist/v/friendsofhyperf/support.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/support)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/support.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/support)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/support)](https://github.com/friendsofhyperf/support)

A comprehensive support component for Hyperf providing essential utilities, helpers, and base classes.

## Features

- 🎯 **Fluent Dispatch API** - Elegant job dispatch with support for async queue, AMQP, and Kafka
- 🔄 **Closure Jobs** - Execute closures as background jobs with dependency injection
- 🛠️ **Helper Functions** - Collection of useful helper functions
- 📦 **Bus System** - Pending dispatch classes for various message systems
- 🧩 **Traits & Utilities** - Reusable traits and utility classes

## Installation

```shell
composer require friendsofhyperf/support
```

## Usage

### Dispatch Helper Function

The `dispatch()` helper function provides a fluent API for dispatching jobs to different systems:

#### Async Queue (Closure Jobs)

```php
use function FriendsOfHyperf\Support\dispatch;

// Simple closure dispatch to async queue
dispatch(function () {
    // Your job logic here
    logger()->info('Job executed!');
});

// With configuration
dispatch(function () {
    // Your job logic here
})
    ->onConnection('high-priority')
    ->delay(60) // Execute after 60 seconds
    ->setMaxAttempts(5);

// With dependency injection
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('Processing ' . count($users) . ' users');
});
```

#### AMQP Producer Messages

```php
use Hyperf\Amqp\Message\ProducerMessageInterface;
use function FriendsOfHyperf\Support\dispatch;

// Dispatch AMQP message
dispatch($amqpMessage)
    ->setPool('default')
    ->setExchange('my.exchange')
    ->setRoutingKey('my.routing.key')
    ->setTimeout(10)
    ->setConfirm(true);
```

#### Kafka Producer Messages

```php
use Hyperf\Kafka\Producer\ProduceMessage;
use function FriendsOfHyperf\Support\dispatch;

// Dispatch Kafka message
dispatch($kafkaMessage)
    ->setPool('default');
```

### CallQueuedClosure

The `CallQueuedClosure` class allows you to execute closures as async queue jobs:

```php
use FriendsOfHyperf\Support\CallQueuedClosure;

// Create a closure job
$job = CallQueuedClosure::create(function () {
    // Your job logic
    return 'Job completed!';
});

// Configure max attempts
$job->setMaxAttempts(3);

// The job can be pushed to queue manually or via dispatch()
```

### Pending Dispatch Classes

#### PendingAsyncQueueDispatch

Fluent API for async queue job dispatch:

```php
use FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch;

$pending = new PendingAsyncQueueDispatch($job);
$pending
    ->onConnection('default')
    ->delay(30)
    ->when($condition, function ($dispatch) {
        $dispatch->onConnection('special');
    })
    ->unless($otherCondition, function ($dispatch) {
        $dispatch->delay(60);
    });
// Job is dispatched when object is destroyed
```

#### PendingAmqpProducerMessageDispatch

Fluent API for AMQP message dispatch:

```php
use FriendsOfHyperf\Support\Bus\PendingAmqpProducerMessageDispatch;

$pending = new PendingAmqpProducerMessageDispatch($message);
$pending
    ->setPool('default')
    ->setExchange('my.exchange')
    ->setRoutingKey('my.routing.key')
    ->setTimeout(5)
    ->setConfirm(true);
// Message is sent when object is destroyed
```

#### PendingKafkaProducerMessageDispatch

Fluent API for Kafka message dispatch:

```php
use FriendsOfHyperf\Support\Bus\PendingKafkaProducerMessageDispatch;

$pending = new PendingKafkaProducerMessageDispatch($message);
$pending->setPool('default');
// Message is sent when object is destroyed
```

### Conditional Execution

All pending dispatch classes support conditional execution:

```php
use function FriendsOfHyperf\Support\dispatch;

dispatch($job)
    ->when($shouldUseHighPriority, function ($dispatch) {
        $dispatch->onConnection('high-priority');
    })
    ->unless($isTestMode, function ($dispatch) {
        $dispatch->delay(10);
    });
```

## API Reference

### dispatch($job)

Creates a pending dispatch instance based on the job type:

- `Closure` → `PendingAsyncQueueDispatch` with `CallQueuedClosure`
- `ProducerMessageInterface` → `PendingAmqpProducerMessageDispatch`
- `ProduceMessage` → `PendingKafkaProducerMessageDispatch`
- Other objects → `PendingAsyncQueueDispatch`

### PendingAsyncQueueDispatch Methods

- `onConnection(string $connection): static` - Set queue connection
- `delay(int $delay): static` - Delay job execution (seconds)
- `setMaxAttempts(int $attempts): static` - Set max retry attempts
- `when(mixed $condition, callable $callback): static` - Conditional execution
- `unless(mixed $condition, callable $callback): static` - Inverse conditional execution

### PendingAmqpProducerMessageDispatch Methods

- `setPool(string $pool): static` - Set AMQP pool name
- `setExchange(string $exchange): static` - Set exchange name
- `setRoutingKey(array|string $routingKey): static` - Set routing key(s)
- `setTimeout(int $timeout): static` - Set timeout (seconds)
- `setConfirm(bool $confirm): static` - Enable/disable confirm mode
- `when(mixed $condition, callable $callback): static` - Conditional execution
- `unless(mixed $condition, callable $callback): static` - Inverse conditional execution

### PendingKafkaProducerMessageDispatch Methods

- `setPool(string $pool): static` - Set Kafka pool name
- `when(mixed $condition, callable $callback): static` - Conditional execution
- `unless(mixed $condition, callable $callback): static` - Inverse conditional execution

### CallQueuedClosure

- `create(Closure $closure): static` - Create a new closure job
- `setMaxAttempts(int $attempts): void` - Set max retry attempts
- `handle(): mixed` - Execute the closure (called by queue worker)

## Architecture Notes

As of v3.1.73, this package includes the core async queue closure functionality:

- Previously in `friendsofhyperf/async-queue-closure-job`
- Moved here to eliminate circular dependencies
- The `async-queue-closure-job` package now depends on this package
- All functionality remains backward compatible

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
