# Telescope

## Available Watchers

- [x] Request Watcher
- [x] Exception Watcher
- [x] Database Query Watcher
- [x] gRPC Request Watcher
- [x] Redis Watcher
- [x] Log Watcher
- [x] Command Line Watcher
- [x] Event Watcher
- [x] HTTP Client Watcher
- [x] Cache Watcher

## Installation

```shell
composer require friendsofhyperf/telescope:~3.1.0
```

Use the `vendor:publish` command to publish its public resources

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope
```

Run the `migrate` command to execute database changes to create and save the data needed by Telescope

```shell
php bin/hyperf.php migrate
```

## Usage

> Choose either listeners or middleware

### Request Listener

Add the listener in the `config/autoload/listeners.php` configuration file

```php
<?php

return [
    FriendsOfHyperf\Telescope\Listener\RequestHandledListener::class,
    FriendsOfHyperf\Telescope\Listener\SetRequestLifecycleListener::class,
];
```

### Middleware

Add global middleware in the `config/autoload/middlewares.php` configuration file

To record HTTP requests, use the `http` middleware

```php
<?php

return [
    'http' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];
```

To record gRPC requests, use the `grpc` middleware

```php
<?php

use FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware;

return [
    'grpc' => [
        TelescopeMiddleware::class,
    ],
];
```

## View Dashboard

`http://127.0.0.1:9501/telescope`

## Database Configuration

Manage database connection configuration in `config/autoload/telescope.php`, uses `default` connection by default

```php
'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
```

## Tags

You may want to attach your own custom tags to entries. To do this, you can use the **`Telescope::tag`** method.

## Batch Filtering

You might want to record entries only under certain special conditions. For this, you can use the **`Telescope::filter`** method.

Example

```php
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use FriendsOfHyperf\Telescope\IncomingEntry;

class TelescopeInitListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        // attach your own custom tags
        Telescope::tag(function (IncomingEntry $entry) {
            if ($entry->type === 'request') {
                return [
                    'status:' . $entry->content['response_status'],
                    'uri:'. $entry->content['uri'],
                ];
            }
        });

        // filter entry
        Telescope::filter(function (IncomingEntry $entry): bool {
            if ($entry->type === 'request'){
                if ($entry->content['uri'] == 'xxxx') {
                    return false;
                }
            }
            return true;
        });
    }
}
```
