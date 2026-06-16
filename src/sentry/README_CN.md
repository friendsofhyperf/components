# Sentry

[English](README.md)

Hyperf 的 Sentry 组件。它将 Sentry PHP SDK 接入 Hyperf 应用生命周期、日志、
链路追踪、指标、定时任务和协程友好的传输层。

## 安装

```shell
composer require friendsofhyperf/sentry
```

可选集成需要安装对应的 Hyperf 或客户端包，例如 `hyperf/amqp`、`hyperf/crontab`、
`hyperf/database`、`hyperf/rpc-multiplex`、`elasticsearch/elasticsearch`、
`phpmyadmin/sql-parser` 或 `hyperf/engine`。

## 发布配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

该命令会发布 `config/autoload/sentry.php`。核心配置如下：

```php
<?php

return [
    'dsn' => env('SENTRY_DSN', ''),
    'release' => env('SENTRY_RELEASE'),
    'environment' => env('APP_ENV', 'production'),
    'sample_rate' => env('SENTRY_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_SAMPLE_RATE'),
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_TRACES_SAMPLE_RATE'),
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE') === null ? null : (float) env('SENTRY_PROFILES_SAMPLE_RATE'),
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', true),
];
```

配置文件还支持 Sentry SDK 选项，例如 `server_name`、`traces_sampler`、
`before_send_log`、`before_send_metric`、`before_send_check_in`、
`before_send_transaction`、`ignore_exceptions` 和 `ignore_transactions`。

## 日志

组件会在未配置 Sentry 日志通道时注册 `logger.channels.sentry`，处理器为
`FriendsOfHyperf\Sentry\Monolog\LogsHandler`。使用下面的环境变量控制最低等级：

```env
SENTRY_ENABLE_LOGS=true
SENTRY_LOGS_CHANNEL_LEVEL=debug
```

如需输出 Sentry SDK 自身的诊断日志，配置一个 PSR logger：

```php
<?php

return [
    'logger' => Hyperf\Contract\StdoutLoggerInterface::class,
];
```

## 命令

```shell
php bin/hyperf.php sentry:about
php bin/hyperf.php sentry:test
php bin/hyperf.php sentry:test --dsn=https://examplePublicKey@o0.ingest.sentry.io/0
php bin/hyperf.php sentry:test --transaction=1
```

`sentry:about` 会输出 SDK、DSN、环境、发布版本、采样率和 PII 状态。
`sentry:test` 会发送测试异常，也可以发送测试事务。

## 注解

```php
<?php

use FriendsOfHyperf\Sentry\Annotation\Breadcrumb;
use FriendsOfHyperf\Sentry\Annotation\Graceful;
use FriendsOfHyperf\Sentry\Annotation\IgnoreException;
use FriendsOfHyperf\Sentry\Metrics\Annotation\Counter;
use FriendsOfHyperf\Sentry\Metrics\Annotation\Histogram;
use FriendsOfHyperf\Sentry\Tracing\Annotation\Trace;

#[IgnoreException]
class IgnoredDomainException extends RuntimeException
{
}

class UserService
{
    #[Breadcrumb(category: 'user')]
    #[Trace(op: 'service.user', description: 'Create user')]
    #[Counter('user_create_total')]
    #[Histogram('user_create_duration')]
    public function create(array $payload): void
    {
        // ...
    }

    #[Graceful(strategy: Graceful::STRATEGY_SWALLOW, report: true)]
    public function reportableFallback(): mixed
    {
        // ...
    }
}
```

`Graceful` 支持 `swallow`、`rethrow`、`fallback` 和 `translate` 四种策略。

## 链路追踪

组件默认启用链路追踪，并可以在 HTTP、RPC、队列、Kafka、AMQP、定时任务、命令和
协程边界之间延续 trace context。

```env
SENTRY_TRACING_ENABLE_AMQP=true
SENTRY_TRACING_ENABLE_ASYNC_QUEUE=true
SENTRY_TRACING_ENABLE_COMMAND=true
SENTRY_TRACING_ENABLE_COROUTINE=true
SENTRY_TRACING_ENABLE_CRONTAB=true
SENTRY_TRACING_ENABLE_KAFKA=true
SENTRY_TRACING_ENABLE_MISSING_ROUTES=true
SENTRY_TRACING_ENABLE_REQUEST=true

SENTRY_TRACING_SPANS_CACHE=true
SENTRY_TRACING_SPANS_COORDINATOR=false
SENTRY_TRACING_SPANS_COROUTINE=true
SENTRY_TRACING_SPANS_DB=true
SENTRY_TRACING_SPANS_ELASTICSEARCH=true
SENTRY_TRACING_SPANS_FILESYSTEM=true
SENTRY_TRACING_SPANS_GRPC=true
SENTRY_TRACING_SPANS_GUZZLE=true
SENTRY_TRACING_SPANS_REDIS=true
SENTRY_TRACING_SPANS_RPC=true
SENTRY_TRACING_SPANS_SQL_QUERIES=true
SENTRY_TRACING_SPANS_VIEW=true
```

在 `config/autoload/sentry.php` 中使用 `ignore_commands` 和
`ignore_transactions` 排除噪音命令或路由。使用 `tracing_tags` 控制 SQL bindings、
结果、响应体等可选 span 数据。

可通过辅助函数进行手动埋点：

```php
<?php

use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

trace(function () {
    return doSomething();
}, SpanContext::make()->setOp('task')->setDescription('Do something'));
```

## 指标

```env
SENTRY_ENABLE_METRICS=true
SENTRY_ENABLE_DEFAULT_METRICS=true
SENTRY_ENABLE_COMMAND_METRICS=true
SENTRY_ENABLE_POOL_METRICS=true
SENTRY_ENABLE_QUEUE_METRICS=true
SENTRY_METRICS_INTERVAL=10
```

默认指标覆盖请求耗时、协程服务器统计、命令耗时、数据库和 Redis 连接池，以及存在
相关包时的异步队列。`#[Counter]` 和 `#[Histogram]` 可添加方法级自定义指标。

## 定时任务

Hyperf crontab 事件可以上报为 Sentry check-in：

```env
SENTRY_CRONS_ENABLE=true
SENTRY_CRONS_CHECKIN_MARGIN=5
SENTRY_CRONS_MAX_RUNTIME=15
SENTRY_CRONS_TIMEZONE=UTC
```

单个 crontab 可以通过 options 覆盖 `checkin_margin`、`max_runtime`、
`failure_issue_threshold`、`recovery_threshold` 和 `update_monitor_config`。
将 `monitor` 设为 `false` 可跳过该任务的 check-in。

## 传输

默认绑定使用 `FriendsOfHyperf\Sentry\Transport\CoHttpTransport`。使用下面的环境
变量调整队列和并发：

```env
SENTRY_TRANSPORT_CHANNEL_SIZE=65535
SENTRY_TRANSPORT_CONCURRENT_LIMIT=1000
SENTRY_HTTP_TIMEOUT=2.0
```

## Sentry 开发文档

- OpenTelemetry 语义约定 [semantic-conventions](https://github.com/open-telemetry/semantic-conventions/tree/main)
- Sentry Span 操作命名规范 [span-operations](https://develop.sentry.dev/sdk/performance/span-operations/#database)
