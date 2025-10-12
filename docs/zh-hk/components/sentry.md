# Sentry

Hyperf 的 Sentry 組件。

## 安裝

```shell
composer require friendsofhyperf/sentry
```

## 發佈配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

## 註冊 LoggerHandler

```php
<?php

# use it send customer log to sentry
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

## 配置 Sentry 運行日誌

```php
<?php

# config/autoload/sentry.php
return [
    // ...
    'logger' => Hyperf\Contract\StdoutLoggerInterface::class,
    // ...
];
```

## 註解

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

## 鏈路追蹤

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

## Sentry 開發文檔

- OpenTelemetry 語義約定 [semantic-conventions](https://github.com/open-telemetry/semantic-conventions/tree/main)
- Sentry Span 操作命名規範 [span-operations](https://develop.sentry.dev/sdk/performance/span-operations/#database)
