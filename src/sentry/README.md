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

## Register Logger Handler

```php
<?php

# use it send customer log to sentry
//\FriendsOfHyperf\Helpers\logs('project-name', 'sentry')->warning('this is a test warning issue!');

return [
    // ...
    'sentry' => [
        'handler' => [
            'class' => FriendsOfHyperf\Sentry\SentryHandler::class,
            'constructor' => [
                'level' => \Monolog\Level::Debug,
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

## Sentry Running log

```php
<?php

# config/autoload/sentry.php
return [
    // ...
    'logger' => Hyperf\Contract\StdoutLoggerInterface::class,
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

## Tracing

```php
<?php

# config/autoload/listeners.php
return [
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingAmqpListener::class,
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingAsyncQueueListener::class,
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingCommandListener::class,
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingCrontabListener::class,
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingDbQueryListener::class,
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingKafkaListener::class,
    FriendsOfHyperf\Sentry\Tracing\Listener\TracingRequestListener::class,
];
```

## Donate

> If you like them, Buy me a cup of coffee.

| Alipay | WeChat |
|  ----  |  ----  |
| <img src="https://hdj.me/images/alipay-min.jpg" width="200" height="200" />  | <img src="https://hdj.me/images/wechat-pay-min.jpg" width="200" height="200" /> |

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
