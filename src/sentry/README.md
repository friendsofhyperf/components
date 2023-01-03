# Sentry

[![Latest Test](https://github.com/friendsofhyperf/sentry/workflows/tests/badge.svg)](https://github.com/friendsofhyperf/sentry/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/friendsofhyperf/sentry.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/sentry)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/sentry.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/sentry)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/sentry)](https://github.com/friendsofhyperf/sentry)

The Sentry component for Hyperf.

## Installation

```shell
composer require friendsofhyperf/sentry
```

## Publish config file

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

## Register exception handler

```php
return [
    'handler' => [
        'http' => [
            FriendsOfHyperf\Sentry\SentryExceptionHandler::class,
            App\Exception\Handler\AppExceptionHandler::class,
        ],
    ],
];
```

## Register logger handler

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
