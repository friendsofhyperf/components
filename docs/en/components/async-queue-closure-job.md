# Async Queue Closure Job

## Introduction

`friendsofhyperf/async-queue-closure-job` is an asynchronous queue closure job component for Hyperf. It allows you to execute closures as background tasks, with full support for dependency injection and fluent configuration, making the use of asynchronous tasks simpler and more elegant.

Unlike the traditional approach of creating task classes, this component enables you to define task logic directly using closures, eliminating the need for additional class files and resulting in cleaner code.

## Installation

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## Basic Usage

### Simple Closure Task

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Dispatch a simple closure task
dispatch(function () {
    // Your task logic
    var_dump('Hello from closure job!');
});
```

### Setting Maximum Attempts

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Set maximum attempts (retry limit)
dispatch(function () {
    // Your task logic
    // If it fails, it will retry up to 3 times
})->setMaxAttempts(3);
```

## Advanced Usage

### Fluent API Configuration

Through method chaining, you can flexibly configure various options for the task:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Chain multiple configuration options
dispatch(function () {
    // Your task logic
})
    ->onPool('high-priority')  // Specify queue connection
    ->delay(60)                      // Delay execution by 60 seconds
    ->setMaxAttempts(5);             // Retry up to 5 times
```

### Specifying Queue Connection

When you have multiple queue connections, you can specify which connection the task should use:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Use the specified queue connection
dispatch(function () {
    // High-priority task logic
})->onPool('high-priority');

// Or use the onPool method (alias)
dispatch(function () {
    // Low-priority task logic
})->onPool('low-priority');
```

### Delayed Execution

You can set the task to execute after a certain period:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Execute after a 60-second delay
dispatch(function () {
    // Your task logic
})->delay(60);

// Execute after a 5-minute delay
dispatch(function () {
    // Your task logic
})->delay(300);
```

### Conditional Execution

Use the `when` and `unless` methods to dynamically configure tasks based on conditions:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$isUrgent = true;

// Execute callback only when the condition is true
dispatch(function () {
    // Your task logic
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onPool('urgent');
    });

// Execute callback only when the condition is false
dispatch(function () {
    // Your task logic
})
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(300);
    });

// Combined usage
dispatch(function () {
    // Your task logic
})
    ->when($isUrgent, function ($dispatch) {
        $dispatch->onPool('urgent');
    })
    ->unless($isUrgent, function ($dispatch) {
        $dispatch->delay(60);
    });
```

### Dependency Injection

Closure tasks fully support Hyperf's dependency injection. You can declare required dependencies in the closure parameters:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;
use App\Service\UserService;
use Psr\Log\LoggerInterface;

// Automatic dependency injection
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('Processing ' . count($users) . ' users');
    
    foreach ($users as $user) {
        // Process user...
    }
});
```

### Using Captured Variables

You can use external variables in the closure via the `use` keyword:

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$userId = 123;
$action = 'update';

// Using captured variables
dispatch(function (UserService $userService) use ($userId, $action) {
    $user = $userService->find($userId);
    
    if ($action === 'update') {
        $userService->update($user);
    }
})->setMaxAttempts(3);
```

## Practical Application Scenarios

### Sending Notifications

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
})->setMaxAttempts(3);
```

### Handling File Uploads

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

The main dispatch function used to create closure tasks.

**Parameters:**
- `$closure` - The closure to execute

**Returns:**
- `PendingAsyncQueueDispatch` - The pending closure dispatch object

### `PendingAsyncQueueDispatch` Methods

#### `onPool(string $pool): static`

Set the queue connection name.

**Parameters:**
- `$pool` - Queue connection name

**Returns:**
- `static` - Current object, supports method chaining

#### `delay(int $delay): static`

Set the delay execution time.

**Parameters:**
- `$delay` - Delay time in seconds

**Returns:**
- `static` - Current object, supports method chaining

#### `setMaxAttempts(int $maxAttempts): static`

Set the maximum number of retry attempts.

**Parameters:**
- `$maxAttempts` - Maximum number of attempts

**Returns:**
- `static` - Current object, supports method chaining

#### `when($condition, $callback): static`

Execute callback when the condition is true.

**Parameters:**
- `$condition` - Condition expression
- `$callback` - Callback function that receives the current object as a parameter

**Returns:**
- `static` - Current object, supports method chaining

#### `unless($condition, $callback): static`

Execute callback when the condition is false.

**Parameters:**
- `$condition` - Condition expression
- `$callback` - Callback function that receives the current object as a parameter

**Returns:**
- `static` - Current object, supports method chaining

## Supported Closure Types

This component supports the following types of closures:

- ✅ Simple closures without parameters
- ✅ Closures with dependency injection
- ✅ Closures using captured variables (`use`)
- ✅ Closures with nullable parameters
- ✅ Closures mixing dependency injection and captured variables

## Notes

1. **Serialization Limitations**: Closures are serialized and stored, therefore:
   - Cannot capture unserializable resources (such as database connections, file handles, etc.)
   - Captured objects should be serializable

2. **Dependency Injection**: Dependencies in closures are resolved from the container when the task executes and are not serialized

3. **Asynchronous Execution**: Tasks are executed asynchronously; the dispatch function returns immediately without waiting for task completion

4. **Error Handling**: Failed tasks will be retried according to the number set by `setMaxAttempts`

## Configuration

This component uses Hyperf's asynchronous queue configuration. You can configure queue parameters in `config/autoload/async_queue.php`:

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

## Comparison with Traditional Task Classes

### Traditional Approach

```php
// Need to create a task class
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

// Dispatch task
$driver->push(new SendNotificationJob($userId, $message));
```

### Using Closure Tasks

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Use closure directly, no need to create a class
dispatch(function (NotificationService $notification) use ($userId, $message) {
    $notification->send($userId, $message);
});
```

Advantages of closure tasks:
- Cleaner code, no need for additional class files
- Better readability, task logic is right where it's dispatched
- Full support for dependency injection
- Flexible fluent API configuration

## Related Components

- [hyperf/async-queue](https://hyperf.wiki/3.1/#/en/async-queue) - Hyperf Asynchronous Queue
- [friendsofhyperf/closure-job](https://github.com/friendsofhyperf/components/tree/main/src/closure-job) - General Closure Job Component