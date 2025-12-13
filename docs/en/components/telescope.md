# Telescope

## Available Listeners

- [x] Request Monitor
- [x] Exception Monitor
- [x] Data Query Monitor
- [x] gRPC Request Monitor
- [x] Redis Monitor
- [x] Log Monitor
- [x] Command Line Monitor
- [x] Event Monitor
- [x] HTTP Client Monitor
- [x] Cache Monitor
- [x] Scheduled Task Monitor

## Installation

```shell
composer require friendsofhyperf/telescope:~3.1.0
```

Use the `vendor:publish` command to publish its public resources

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/telescope
```

Run the `migrate` command to execute database changes to create and store the data required by Telescope

```shell
php bin/hyperf.php migrate
```

## Usage

### Middleware (Optional for gRPC)

Add the middleware in the `config/autoload/middlewares.php` configuration file

To enable additional gRPC functionality, use the `grpc` middleware

```php
<?php

return [
    'grpc' => [
        FriendsOfHyperf\Telescope\Middleware\TelescopeMiddleware::class,
    ],
];
```

> Note: Request tracking is automatically enabled via the RequestHandledListener. The TelescopeMiddleware is only needed for additional gRPC-specific functionality.

## View Dashboard

`http://127.0.0.1:9501/telescope`

## Database Configuration

Manage the database connection configuration in `config/autoload/telescope.php`, defaulting to the `default` connection

```php
'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
```

## Tags

You may wish to attach your own custom tags to entries. To do this, you can use the **`Telescope::tag`** method.

## Batch Filtering

You may want to record entries only under certain special conditions. To do this, you can use the **`Telescope::filter`** method.

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