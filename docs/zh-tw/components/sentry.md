# Sentry

Hyperf 的 Sentry 元件。它將 Sentry PHP SDK 接入 Hyperf 應用生命週期、日誌、
鏈路追蹤、指標、定時任務和協程友好的傳輸層。

## 安裝

```shell
composer require friendsofhyperf/sentry
```

可選整合需要安裝對應的 Hyperf 或客戶端包，例如 `hyperf/amqp`、`hyperf/crontab`、
`hyperf/database`、`hyperf/rpc-multiplex`、`elasticsearch/elasticsearch`、
`phpmyadmin/sql-parser` 或 `hyperf/engine`。

## 釋出配置

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

該命令會發布 `config/autoload/sentry.php`。核心配置如下：

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

配置檔案還支援 Sentry SDK 選項，例如 `server_name`、`traces_sampler`、
`before_send_log`、`before_send_metric`、`before_send_check_in`、
`before_send_transaction`、`ignore_exceptions` 和 `ignore_transactions`。

## 日誌

元件會在未配置 Sentry 日誌通道時註冊 `logger.channels.sentry`，處理器為
`FriendsOfHyperf\Sentry\Monolog\LogsHandler`。使用下面的環境變數控制最低等級：

```env
SENTRY_ENABLE_LOGS=true
SENTRY_LOGS_CHANNEL_LEVEL=debug
```

如需輸出 Sentry SDK 自身的診斷日誌，配置一個 PSR logger：

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

`sentry:about` 會輸出 SDK、DSN、環境、釋出版本、取樣率和 PII 狀態。
`sentry:test` 會發送測試異常，也可以傳送測試事務。

## 註解

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

`Graceful` 支援 `swallow`、`rethrow`、`fallback` 和 `translate` 四種策略。

## 鏈路追蹤

元件預設啟用鏈路追蹤，並可以在 HTTP、RPC、佇列、Kafka、AMQP、定時任務、命令和
協程邊界之間延續 trace context。

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
結果、響應體等可選 span 資料。

可透過輔助函式進行手動埋點：

```php
<?php

use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

trace(function () {
    return doSomething();
}, SpanContext::make()->setOp('task')->setDescription('Do something'));
```

## 指標

```env
SENTRY_ENABLE_METRICS=true
SENTRY_ENABLE_DEFAULT_METRICS=true
SENTRY_ENABLE_COMMAND_METRICS=true
SENTRY_ENABLE_POOL_METRICS=true
SENTRY_ENABLE_QUEUE_METRICS=true
SENTRY_METRICS_INTERVAL=10
```

預設指標覆蓋請求耗時、協程伺服器統計、命令耗時、資料庫和 Redis 連線池，以及存在
相關包時的非同步佇列。`#[Counter]` 和 `#[Histogram]` 可新增方法級自定義指標。

## 定時任務

Hyperf crontab 事件可以上報為 Sentry check-in：

```env
SENTRY_CRONS_ENABLE=true
SENTRY_CRONS_CHECKIN_MARGIN=5
SENTRY_CRONS_MAX_RUNTIME=15
SENTRY_CRONS_TIMEZONE=UTC
```

單個 crontab 可以透過 options 覆蓋 `checkin_margin`、`max_runtime`、
`failure_issue_threshold`、`recovery_threshold` 和 `update_monitor_config`。
將 `monitor` 設為 `false` 可跳過該任務的 check-in。

## 傳輸

預設繫結使用 `FriendsOfHyperf\Sentry\Transport\CoHttpTransport`。使用下面的環境
變數調整佇列和併發：

```env
SENTRY_TRANSPORT_CHANNEL_SIZE=65535
SENTRY_TRANSPORT_CONCURRENT_LIMIT=1000
SENTRY_HTTP_TIMEOUT=2.0
```

## Sentry 開發文件

- OpenTelemetry 語義約定 [semantic-conventions](https://github.com/open-telemetry/semantic-conventions/tree/main)
- Sentry Span 操作命名規範 [span-operations](https://develop.sentry.dev/sdk/performance/span-operations/#database)
