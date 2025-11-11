# Async Queue Closure Job

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/async-queue-closure-job)](https://packagist.org/packages/friendsofhyperf/async-queue-closure-job)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/async-queue-closure-job)](https://packagist.org/packages/friendsofhyperf/async-queue-closure-job)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/async-queue-closure-job)](https://github.com/friendsofhyperf/async-queue-closure-job)

The async queue closure job component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/async-queue-closure-job
```

## Usage

```php
use function FriendsOfHyperf\AsyncQueueClosureJob\dispatch;

// Dispatch a closure as async queue job
dispatch(function () {
    // Your job logic here
    var_dump('Hello from closure job!');
});

// With custom queue name
dispatch(function () {
    // Your job logic here
}, 'custom-queue');

// With delay (in seconds)
dispatch(function () {
    // Your job logic here
}, 'default', 10); // Execute after 10 seconds

// With max attempts
dispatch(function () {
    // Your job logic here
}, 'default', 0, 3); // 3 max attempts
```

You can also use the generic dispatch helper from the helpers component:

```php
use function FriendsOfHyperf\Helpers\dispatch;

dispatch(function () {
    // Your job logic here
}, 'default', 0, 3); // queue name, delay, max attempts
```

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
