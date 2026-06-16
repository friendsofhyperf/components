# Sentry

[中文说明](README_CN.md)

[![Latest Version](https://img.shields.io/packagist/v/friendsofhyperf/sentry.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/sentry)
[![Total Downloads](https://img.shields.io/packagist/dt/friendsofhyperf/sentry.svg?style=flat-square)](https://packagist.org/packages/friendsofhyperf/sentry)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/sentry)](https://github.com/friendsofhyperf/sentry)

Sentry component for Hyperf. It integrates the Sentry PHP SDK with Hyperf
application lifecycle events, logs, tracing, metrics, crons, and coroutine-friendly
transport.

## Installation

```shell
composer require friendsofhyperf/sentry
```

Optional integrations require the related Hyperf or client packages, for example
`hyperf/amqp`, `hyperf/crontab`, `hyperf/database`, `hyperf/rpc-multiplex`,
`elasticsearch/elasticsearch`, `phpmyadmin/sql-parser`, or `hyperf/engine`.

## Publish Configuration

```shell
php bin/hyperf.php vendor:publish friendsofhyperf/sentry
```

This publishes `config/autoload/sentry.php`. The core settings are:

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

The configuration file also accepts Sentry SDK options such as `server_name`,
`traces_sampler`, `before_send_log`, `before_send_metric`,
`before_send_check_in`, `before_send_transaction`, `ignore_exceptions`, and
`ignore_transactions`.

## Logs

The component registers a `logger.channels.sentry` channel with
`FriendsOfHyperf\Sentry\Monolog\LogsHandler` when no Sentry logger channel is
already configured. Configure the minimum level with:

```env
SENTRY_ENABLE_LOGS=true
SENTRY_LOGS_CHANNEL_LEVEL=debug
```

To send diagnostic logs produced by the Sentry SDK itself, configure a PSR logger:

```php
<?php

return [
    'logger' => Hyperf\Contract\StdoutLoggerInterface::class,
];
```

## Commands

```shell
php bin/hyperf.php sentry:about
php bin/hyperf.php sentry:test
php bin/hyperf.php sentry:test --dsn=https://examplePublicKey@o0.ingest.sentry.io/0
php bin/hyperf.php sentry:test --transaction=1
```

`sentry:about` prints SDK, DSN, environment, release, sample rate, and PII status.
`sentry:test` sends a test exception and can also send a test transaction.

## Annotations

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

Available graceful strategies are `swallow`, `rethrow`, `fallback`, and
`translate`.

## Tracing

The component enables tracing by default and can continue trace context across
HTTP, RPC, queue, Kafka, AMQP, crontab, command, and coroutine boundaries.

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

Use `ignore_commands` and `ignore_transactions` in `config/autoload/sentry.php`
to exclude noisy commands or routes. Use `tracing_tags` to control optional span
data such as SQL bindings, results, or response bodies.

Manual instrumentation is available through helper functions:

```php
<?php

use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;

trace(function () {
    return doSomething();
}, SpanContext::make()->setOp('task')->setDescription('Do something'));
```

## Metrics

```env
SENTRY_ENABLE_METRICS=true
SENTRY_ENABLE_DEFAULT_METRICS=true
SENTRY_ENABLE_COMMAND_METRICS=true
SENTRY_ENABLE_POOL_METRICS=true
SENTRY_ENABLE_QUEUE_METRICS=true
SENTRY_METRICS_INTERVAL=10
```

Default metrics cover request timing, coroutine server stats, command timing,
database and Redis pools, and async queues when the related packages are present.
`#[Counter]` and `#[Histogram]` add method-level custom metrics.

## Crons

Hyperf crontab events can be reported as Sentry check-ins:

```env
SENTRY_CRONS_ENABLE=true
SENTRY_CRONS_CHECKIN_MARGIN=5
SENTRY_CRONS_MAX_RUNTIME=15
SENTRY_CRONS_TIMEZONE=UTC
```

Per-crontab options can override `checkin_margin`, `max_runtime`,
`failure_issue_threshold`, `recovery_threshold`, and `update_monitor_config`.
Set `monitor` to `false` on a crontab to skip check-ins for that task.

## Transport

The default binding uses `FriendsOfHyperf\Sentry\Transport\CoHttpTransport`.
Tune its queue and concurrency with:

```env
SENTRY_TRANSPORT_CHANNEL_SIZE=65535
SENTRY_TRANSPORT_CONCURRENT_LIMIT=1000
SENTRY_HTTP_TIMEOUT=2.0
```

## Sentry Development Documentation

- OpenTelemetry Semantic Conventions [semantic-conventions](https://github.com/open-telemetry/semantic-conventions/tree/main)
- Sentry Span Operations Naming Standards [span-operations](https://develop.sentry.dev/sdk/performance/span-operations/#database)

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
