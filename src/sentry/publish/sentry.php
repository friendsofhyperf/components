<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    'dsn' => env('SENTRY_DSN', ''),

    // The release version of your application
    // Example with dynamic git hash: trim(exec('git log --pretty="%h" -n1 HEAD'))
    'release' => env('SENTRY_RELEASE'),

    // When left empty or `null` the environment will be used (usually discovered from `APP_ENV` in your `.env`)
    'environment' => env('APP_ENV', 'production'),

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#server_name
    // 'server_name' => env('SENTRY_SERVER_NAME', gethostname()),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#sample_rate
    'sample_rate' => env('SENTRY_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_SAMPLE_RATE'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#traces_sample_rate
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_TRACES_SAMPLE_RATE'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#traces_sampler
    // 'traces_sampler' => function (Sentry\Tracing\SamplingContext $context): float {
    //     if (str_contains($context->getTransactionContext()->getDescription(), '/health')) {
    //         return 0;
    //     }
    //     return env('SENTRY_TRACES_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_TRACES_SAMPLE_RATE');
    // },

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#profiles_sample_rate
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE') === null ? null : (float) env('SENTRY_PROFILES_SAMPLE_RATE'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#enable_logs
    'enable_logs' => env('SENTRY_ENABLE_LOGS', true),

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#before_send_log
    // 'before_send_log' => function (Sentry\Logs\Log $log): Sentry\Logs\Log {
    //     return $log;
    // },

    'logs_channel_level' => env('SENTRY_LOGS_CHANNEL_LEVEL', Sentry\Logs\LogLevel::debug()),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#enable_metrics
    'enable_metrics' => env('SENTRY_ENABLE_METRICS', true),

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#before_send_metric
    // 'before_send_metric' => function (Sentry\Metrics\Types\Metric $metric): ?Sentry\Metrics\Types\Metric {
    //     return $metric;
    // },

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#before_send_check_in
    // 'before_send_check_in' => function (Sentry\Event $event): Sentry\Event {
    //     return $event;
    // },

    'enable_default_metrics' => env('SENTRY_ENABLE_DEFAULT_METRICS', true),
    'enable_command_metrics' => env('SENTRY_ENABLE_COMMAND_METRICS', true),
    'enable_pool_metrics' => env('SENTRY_ENABLE_POOL_METRICS', true),
    'enable_queue_metrics' => env('SENTRY_ENABLE_QUEUE_METRICS', true),
    'metrics_interval' => (int) env('SENTRY_METRICS_INTERVAL', 10),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#send_default_pii
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', true),

    // Must instanceof Psr\Log\LoggerInterface
    // 'logger' => Hyperf\Contract\StdoutLoggerInterface::class,

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#ignore_exceptions
    'ignore_exceptions' => [
        Hyperf\HttpMessage\Exception\NotFoundHttpException::class,
        Hyperf\HttpMessage\Exception\MethodNotAllowedHttpException::class,
        Hyperf\Validation\ValidationException::class,
    ],

    // @see: before_send_transaction
    // 'before_send_transaction' => function (Sentry\Event $event): Sentry\Event {
    //     return $event;
    // },

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#ignore_transactions
    'ignore_transactions' => [
        'GET /health',
    ],

    // Enable or disable specific integrations
    'enable' => [
        'amqp' => env('SENTRY_ENABLE_AMQP', true),
        'async_queue' => env('SENTRY_ENABLE_ASYNC_QUEUE', true),
        'command' => env('SENTRY_ENABLE_COMMAND', true),
        'crontab' => env('SENTRY_ENABLE_CRONTAB', true),
        'kafka' => env('SENTRY_ENABLE_KAFKA', true),
        'request' => env('SENTRY_ENABLE_REQUEST', true),
    ],

    // Breadcrumbs configuration
    'breadcrumbs' => [
        'async_queue' => env('SENTRY_BREADCRUMBS_ASYNC_QUEUE', true),
        'cache' => env('SENTRY_BREADCRUMBS_CACHE', true),
        'command' => env('SENTRY_BREADCRUMBS_COMMAND', true),
        'command_input' => env('SENTRY_BREADCRUMBS_COMMAND_INPUT', true),
        'filesystem' => env('SENTRY_BREADCRUMBS_FILESYSTEM', true),
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES', true),
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS', true),
        'sql_transaction' => env('SENTRY_BREADCRUMBS_SQL_TRANSACTION', true),
        'redis' => env('SENTRY_BREADCRUMBS_REDIS', true),
        'guzzle' => env('SENTRY_BREADCRUMBS_GUZZLE', true),
        'logs' => env('SENTRY_BREADCRUMBS_LOGS', true),
    ],

    // Additional integrations to register
    'integrations' => [
    ],

    // Commands to ignore for performance monitoring
    'ignore_commands' => [
        'crontab:run',
        'gen:*',
        'migrate*',
        'sentry:test',
        'tinker',
        'vendor:publish',
    ],

    // Performance monitoring specific configuration
    'tracing' => [
        'amqp' => env('SENTRY_TRACING_ENABLE_AMQP', true),
        'async_queue' => env('SENTRY_TRACING_ENABLE_ASYNC_QUEUE', true),
        'command' => env('SENTRY_TRACING_ENABLE_COMMAND', true),
        'crontab' => env('SENTRY_TRACING_ENABLE_CRONTAB', true),
        'kafka' => env('SENTRY_TRACING_ENABLE_KAFKA', true),
        'missing_routes' => env('SENTRY_TRACING_ENABLE_MISSING_ROUTES', true),
        'request' => env('SENTRY_TRACING_ENABLE_REQUEST', true),
    ],

    // Enable or disable specific tracing spans
    'tracing_spans' => [
        'cache' => env('SENTRY_TRACING_SPANS_CACHE', true),
        'coroutine' => env('SENTRY_TRACING_SPANS_COROUTINE', true),
        'db' => env('SENTRY_TRACING_SPANS_DB', true),
        'elasticsearch' => env('SENTRY_TRACING_SPANS_ELASTICSEARCH', true),
        'filesystem' => env('SENTRY_TRACING_SPANS_FILESYSTEM', true),
        'guzzle' => env('SENTRY_TRACING_SPANS_GUZZLE', true),
        'rpc' => env('SENTRY_TRACING_SPANS_RPC', true),
        'grpc' => env('SENTRY_TRACING_SPANS_GRPC', true),
        'redis' => env('SENTRY_TRACING_SPANS_REDIS', true),
        'sql_queries' => env('SENTRY_TRACING_SPANS_SQL_QUERIES', true),
        'view' => env('SENTRY_TRACING_SPANS_VIEW', true),
    ],

    // Whether to include the given tags in tracing spans
    'tracing_tags' => [
        'amqp.result' => false,
        'annotation.result' => false,
        'db.sql.bindings' => true,
        'db.result' => false,
        'elasticsearch.result' => false,
        'http.response.body.contents' => false,
        'redis.result' => false,
        'rpc.result' => false,
    ],

    // Crons configuration
    'crons' => [
        'enable' => env('SENTRY_CRONS_ENABLE', true),
        'checkin_margin' => (int) env('SENTRY_CRONS_CHECKIN_MARGIN', 5),
        'max_runtime' => (int) env('SENTRY_CRONS_MAX_RUNTIME', 15),
        'timezone' => env('SENTRY_CRONS_TIMEZONE', date_default_timezone_get()),
    ],

    // Transport configuration
    'transport_channel_size' => (int) env('SENTRY_TRANSPORT_CHANNEL_SIZE', 65535),
    'transport_concurrent_limit' => (int) env('SENTRY_TRANSPORT_CONCURRENT_LIMIT', 1000),

    'http_timeout' => (float) env('SENTRY_HTTP_TIMEOUT', 2.0),
];
