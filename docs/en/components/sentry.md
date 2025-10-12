# Sentry

Hyperf's Sentry component.

## Installation

```shell
composer require friendsofhyperf/sentry
```

## Publish Configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

## Register LoggerHandler

```php
<?php

# Use it to send custom logs to Sentry
//\FriendsOfHyperf\Helpers\logs('project-name', 'sentry')->warning('this is a test warning issue!');

return [
    // ...
    'sentry' => [
        'handler' => [
            'class' => \FriendsOfHyperf\Sentry\Monolog\LogsHandler::class,
            'constructor' => [
                'group' => 'sentry',
                'level' => \Sentry\Logs\LogLevel::debug(),
                'bubble' => true,
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

## Configure Sentry Runtime Logs

```php
<?php

# config/autoload/sentry.php
return [
    // ...
    'logger' => Hyperf\Contract\StdoutLoggerInterface::class,
    // ...
];
```

## Annotations

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

## Distributed Tracing

```env
SENTRY_TRACING_ENABLE_AMQP=true
SENTRY_TRACING_ENABLE_ASYNC_QUEUE=true
SENTRY_TRACING_ENABLE_COMMAND=true
SENTRY_TRACING_ENABLE_CRONTAB=true
SENTRY_TRACING_ENABLE_KAFKA=true
SENTRY_TRACING_ENABLE_MISSING_ROUTES=true
SENTRY_TRACING_ENABLE_REQUEST=true
SENTRY_TRACING_SPANS_COROUTINE=true
SENTRY_TRACING_SPANS_DB=true
SENTRY_TRACING_SPANS_ELASTICSEARCH=true
SENTRY_TRACING_SPANS_GUZZLE=true
SENTRY_TRACING_SPANS_RPC=true
SENTRY_TRACING_SPANS_REDIS=true
SENTRY_TRACING_SPANS_SQL_QUERIES=true
```

## Sentry Development Documentation

- OpenTelemetry Semantic Conventions [semantic-conventions](https://github.com/open-telemetry/semantic-conventions/tree/main)
- Sentry Span Operations Naming Conventions [span-operations](https://develop.sentry.dev/sdk/performance/span-operations/#database)