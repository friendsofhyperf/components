# Sentry

[![Latest Version](https://img.shields.io/packagist/v/friendsofhyperf/sentry.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/sentry)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/sentry.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/sentry)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/sentry)](https://github.com/friendsofhyperf/sentry)

The sentry component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/sentry
```

## Publish Config File

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

## Enable Request Lifecycle

```php
# config/autoload/server.php
return [
    // ...
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 9501,
            'callbacks' => [
                Event::ON_REQUEST => [Hyperf\HttpServer\Server::class, 'onRequest'],
            ],
            'options' => [
                'enable_request_lifecycle' => true,
            ],
        ],
    ],
    // ...
];
```

## Register Logger Handler

```php
<?php

return [
    // ...
    'sentry' => [
        'handler' => [
            'class' => FriendsOfHyperf\Sentry\SentryHandler::class,
            'constructor' => [
                'level' => \Monolog\Logger::DEBUG,
            ],
        ],
        'formatter' => [
            'class' => \Monolog\Formatter\LineFormatter::class,
            'constructor' => [
                'format' => null,
                'dateFormat' => null,
                'allowInlineLineBreaks' => true,
            ]
        ],
    ],
    // ...
];

```

## Annotation

```php
<?php
namespace App;

use FriendsOfHyperf\Sentry\Annotation\Breadcrumb;

class Foo
{
    #[Breadcrumb(category: 'foo')]
    public function bar($a = 1, $b = 2)
    {
        return __METHOD__;
    }
}
```

## Trace

```php
<?php

# config/autoload/middleware.php
return [
    'http' => [
        FriendsOfHyperf\Sentry\Tracing\Middleware\TraceMiddleware::class => PHP_INT_MAX,
    ],
    // rpc
    // 'rpc' => [
    //     FriendsOfHyperf\Sentry\Tracing\Middleware\TraceMiddleware::class,
    // ],
];
```

## Fix un-support `native-curl`

- Check

```shell
php --ri swoole |grep curl
# curl-native => enabled
```

- Fix

```php
composer require php-http/guzzle6-adapter
# or
composer require php-http/guzzle7-adapter
```

## Sponsor

If you like this project, Buy me a cup of coffee. [ [Alipay](https://hdj.me/images/alipay.jpg) | [WePay](https://hdj.me/images/wechat-pay.jpg) ]
