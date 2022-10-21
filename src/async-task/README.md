# Async Task

[![Latest Stable Version](https://img.shields.io/packagist/v/friendsofhyperf/async-task)](https://packagist.org/packages/friendsofhyperf/async-task)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/async-task)](https://packagist.org/packages/friendsofhyperf/async-task)
[![License](https://img.shields.io/packagist/l/friendsofhyperf/async-task)](https://github.com/friendsofhyperf/async-task)

## Installation

```bash
composer require friendsofhyperf/async-task
```

## Adds process

```php
// config/autoload/processes.php
return [
    'processes' => [
        // ...
        Hyperf\AsyncTask\Process\TaskConsumerProcess::class,
    ],
];
```

## Adds listener

```php
// config/autoload/listeners.php

return [
    'listeners' => [
        // ...
        Hyperf\AsyncTask\Listener\TaskHandledListener::class,
    ],
];
```

## Usage

```php
use FriendsOfHyperf\AsyncTask\Task;

class FooTask extends Task
{
    public function handle():void
    {
        var_dump('foo');
    }
}

Task::deliver(new FooTask());

Task::deliver(fn () => var_dump(111));
```
