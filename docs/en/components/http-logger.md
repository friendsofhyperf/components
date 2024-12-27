# Http Logger

An HTTP logging component provided for Hyperf.

## Installation

```shell
composer require "friendsofhyperf/http-logger"
```

## Publish Configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/http-logger
```

## Usage

```php
return [
    'http' => [
        \FriendsOfHyperf\Http\Logger\Middleware\HttpLogger::class,
    ],
];
```