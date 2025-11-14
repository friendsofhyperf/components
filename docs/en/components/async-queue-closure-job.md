# Async Queue Closure Job

## Introduction

`friendsofhyperf/async-queue-closure-job` is an async queue closure job component for Hyperf. It allows you to execute closures as background jobs with full support for dependency injection and fluent configuration, making async tasks simpler and more elegant.

Unlike traditional job classes, this component lets you define job logic directly with closures, eliminating the need for extra class files and making your code more concise.

## Installation

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## Basic Usage

### Simple Closure Job

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Dispatch a simple closure job
dispatch(function () {
    // Your job logic here
    var_dump('Hello from closure job!');
});
```

### Set Maximum Attempts

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Set maximum attempts (retry limit)
dispatch(function () {
    // Your job logic here
    // If it fails, it will retry up to 3 times
})->setMaxAttempts(3);
```

## Advanced Usage

### Fluent API Configuration

You can flexibly configure various options using method chaining:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Chain multiple configuration options
dispatch(function () {
    // Your job logic here
})
    ->onPool('high-priority')  // Specify queue connection
    ->delay(60)                      // Delay execution by 60 seconds
    ->setMaxAttempts(5);             // Retry up to 5 times
```

### Specifying Queue Connections

When you have multiple queue connections, you can specify which connection to use:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Use a specific queue connection
dispatch(function () {
    // High priority task logic
})->onPool('high-priority');

// Alternative pool name
dispatch(function () {
    // Low priority task logic
})->onPool('low-priority');
```

### Delayed Execution

You can set a delay before the task starts executing:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Delay execution by 60 seconds
dispatch(function () {
    // Your job logic here
})->delay(60);

// Delay execution by 5 minutes
dispatch(function () {
    // Your job logic here
})->delay(300);
```

### Conditional Execution

Use `when` and `unless` methods to dynamically configure tasks based on conditions:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$isUrgent = true;

// Execute callback only when condition is true
dispatch(function () {
    // Your job logic here
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onPool('urgent');
    });

// Execute callback only when condition is false
dispatch(function () {
    // Your job logic here
})
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(30);
    });

// Combine usage
dispatch(function () {
    // Your job logic here
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onPool('urgent');
    })
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(60);
    });
```

### Dependency Injection

Closure jobs fully support Hyperf's dependency injection. You can declare required dependencies as closure parameters:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;
use App\Service\UserService;
use Psr\Log\LoggerInterface;

// Automatic dependency injection
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('Processing ' . count($users) . ' users');

    foreach ($users as $user) {
        // Process users...
    }
});
```

### Using Captured Variables

You can use external variables in closures via the `use` keyword:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userId = 123;
$action = 'update';

// Use captured variables
dispatch(function (UserService $userService) use ($userId, $action) {
    $user = $userService->find($userId);

    if ($action === 'update') {
        $userService->update($user);
    }
})->setMaxAttempts(3);
```

## Real-World Use Cases

### Sending Notifications

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
})->setMaxAttempts(3);
```

### File Upload Processing

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (FileService $fileService) use ($filePath) {
    $fileService->process($filePath);
    $fileService->generateThumbnail($filePath);
})->delay(5);
```

### Data Statistics

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (StatisticsService $stats) use ($date) {
    $stats->calculateDailyReport($date);
    $stats->sendReport($date);
})->onPool('statistics');
```

### Batch Operations

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userIds = [1, 2, 3, 4, 5];

foreach ($userIds as $userId) {
    dispatch(function (UserService $userService) use ($userId) {
        $userService->syncUserData($userId);
    })->delay(10 * $userId); // Set different delays for each task
}
```

## API Reference

### `dispatch(Closure $closure): PendingAsyncQueueDispatch`

The main dispatch function that creates a closure job.

**Parameters:**

- `$closure` - The closure to execute

**Returns:**

- `PendingAsyncQueueDispatch` - Pending closure dispatch object

### `PendingAsyncQueueDispatch` Methods

#### `onPool(string $pool): static`

Set the queue connection name.

**Parameters:**

- `$pool` - Queue connection name

**Returns:**

- `static` - Current object for method chaining

#### `delay(int $delay): static`

Set the delay time before execution.

**Parameters:**

- `$delay` - Delay time in seconds

**Returns:**

- `static` - Current object for method chaining

#### `setMaxAttempts(int $maxAttempts): static`

Set the maximum retry attempts.

**Parameters:**

- `$maxAttempts` - Maximum attempts

**Returns:**

- `static` - Current object for method chaining

#### `when($condition, $callback): static`

Execute callback when condition is true.

**Parameters:**

- `$condition` - Condition expression
- `$callback` - Callback function that receives the current object as parameter

**Returns:**

- `static` - Current object for method chaining

#### `unless($condition, $callback): static`

Execute callback when condition is false.

**Parameters:**

- `$condition` - Condition expression
- `$callback` - Callback function that receives the current object as parameter

**Returns:**

- `static` - Current object for method chaining

## Supported Closure Types

This component supports the following closure types:

- ✅ Simple closures without parameters
- ✅ Closures with dependency injection
- ✅ Closures with captured variables (`use`)
- ✅ Closures with nullable parameters
- ✅ Mixing dependency injection and captured variables

## Notes

1. **Serialization Limitations**: Closures are serialized before storage, therefore:
   - Cannot capture non-serializable resources (e.g., database connections, file handles)
   - Captured objects should be serializable

2. **Dependency Injection**: Dependencies in closures will be resolved from the container when the job executes, not serialized

3. **Async Execution**: Tasks execute asynchronously. The dispatch function returns immediately without waiting for task completion

4. **Error Handling**: Failed tasks will retry according to the `setMaxAttempts` configuration

## Configuration

This component uses Hyperf's async queue configuration. You can configure queue parameters in `config/autoload/async_queue.php`:

```php
<?php

return [
    'default' => [
        'driver' => Hyperf\AsyncQueue\Driver\RedisDriver::class,
        'channel' => 'queue',
        'timeout' => 2,
        'retry_seconds' => 5,
        'handle_timeout' => 10,
        'processes' => 1,
    ],
];
```

## Testing

```shell
composer test:unit -- tests/AsyncQueueClosureJob
```

## Comparison with Traditional Job Classes

### Traditional Approach

```php
// Need to create a job class
class SendNotificationJob extends Job
{
    public function __construct(public int $userId, public string $message)
    {
    }

    public function handle()
    {
        $notification = ApplicationContext::getContainer()->get(NotificationService::class);
        $notification->send($this->userId, $this->message);
    }
}

// Dispatch job
$driver->push(new SendNotificationJob($userId, $message));
```

### Using Closure Jobs

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Directly use closure, no need to create a class
dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
});
```

Advantages of closure jobs:

- Cleaner code, no need to create extra class files
- Better readability, job logic is right where it's dispatched
- Full dependency injection support
- Flexible fluent API configuration

## Related Components

- [hyperf/async-queue](https://hyperf.wiki/3.1/#/zh-cn/async-queue) - Hyperf Async Queue
- [friendsofhyperf/closure-job](https://github.com/friendsofhyperf/components/tree/main/src/closure-job) - Generic Closure Job Component
