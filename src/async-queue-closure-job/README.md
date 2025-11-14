# Async Queue Closure Job

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/async-queue-closure-job)](https://packagist.org/packages/friendsofhyperf/async-queue-closure-job)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/async-queue-closure-job)](https://packagist.org/packages/friendsofhyperf/async-queue-closure-job)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/async-queue-closure-job)](https://github.com/friendsofhyperf/async-queue-closure-job)

The async queue closure job component for Hyperf. Execute closures as background jobs with full support for dependency injection, fluent configuration.

## Installation

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## Basic Usage

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Simple closure dispatch
dispatch(function () {
    // Your job logic here
    var_dump('Hello from closure job!');
});

// With max attempts (retry limit)
dispatch(function () {
    // Your job logic here
})->setMaxAttempts(3);
```

## Advanced Usage

### Fluent API Configuration

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Chain multiple configurations
dispatch(function () {
    // Your job logic here
})
    ->onConnection('high-priority')
    ->delay(60) // Execute after 60 seconds
    ->setMaxAttempts(5);
```

### Conditional Execution

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

$condition = true;

// Execute only when condition is true
dispatch(function () {
    // Your job logic here
})
    ->when($condition, function ($dispatch) {
        $dispatch->onConnection('conditional-connection');
    });

// Execute only when condition is false
dispatch(function () {
    // Your job logic here
})
    ->unless($condition, function ($dispatch) {
        $dispatch->delay(30);
    });
```

### Dependency Injection

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Automatic dependency injection
dispatch(function (UserService $userService, LoggerInterface $logger) {
    $users = $userService->getActiveUsers();
    $logger->info('Processing ' . count($users) . ' users');
    // Process users...
});

// With custom parameters
dispatch(function (UserService $userService, int $userId) {
    $user = $userService->find($userId);
    // Process user...
})->setMaxAttempts(3);
```

## API Reference

### `dispatch(Closure $closure): PendingAsyncQueueDispatch`

The main dispatch function that creates a closure job.

### `PendingAsyncQueueDispatch` Methods

- `onConnection(string $connection): static` - Set the connection name
- `delay(int $delay): static` - Set execution delay in seconds
- `setMaxAttempts(int $maxAttempts): static` - Set maximum retry attempts
- `when($condition, $callback): static` - Execute callback when condition is true
- `unless($condition, $callback): static` - Execute callback when condition is false

### Supported Closure Types

- Simple closures without parameters
- Closures with dependency injection
- Closures with captured variables (`use`)
- Closures with nullable parameters

## Testing

Run tests:

```shell
composer test:unit -- tests/AsyncQueueClosureJob
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)

---

## Made with ❤️ for the Hyperf community
