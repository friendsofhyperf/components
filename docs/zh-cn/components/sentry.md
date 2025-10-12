# Sentry

Hyperf 的 Sentry 组件。

## 安装

```shell
composer require friendsofhyperf/sentry
```

## 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

## 注册 LoggerHandler

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

## 配置 Sentry 运行日志

```php
<?php

# config/autoload/sentry.php
return [
    // ...
    'logger' => Hyperf\Contract\StdoutLoggerInterface::class,
    // ...
];
```

## 注解

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

## 链路追踪

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

## Sentry 开发文档

- OpenTelemetry 语义约定 [semantic-conventions](https://github.com/open-telemetry/semantic-conventions/tree/main)
- Sentry Span 操作命名规范 [span-operations](https://develop.sentry.dev/sdk/performance/span-operations/#database)
