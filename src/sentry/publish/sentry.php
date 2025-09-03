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

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#sample_rate
    'sample_rate' => env('SENTRY_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_SAMPLE_RATE'),

    // Switch tracing on/off
    'enable_tracing' => env('SENTRY_ENABLE_TRACING', true),

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
    'enable_logs' => env('SENTRY_ENABLE_LOGS', false),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#send_default_pii
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    // Must instanceof Psr\Log\LoggerInterface
    // 'logger' => Hyperf\Contract\StdoutLoggerInterface::class,

    'enable' => [
        'amqp' => env('SENTRY_ENABLE_AMQP', true),
        'async_queue' => env('SENTRY_ENABLE_ASYNC_QUEUE', true),
        'command' => env('SENTRY_ENABLE_COMMAND', true),
        'crontab' => env('SENTRY_ENABLE_CRONTAB', true),
        'kafka' => env('SENTRY_ENABLE_KAFKA', true),
        'request' => env('SENTRY_ENABLE_REQUEST', true),
    ],

    'breadcrumbs' => [
        'cache' => env('SENTRY_BREADCRUMBS_CACHE', true),
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES', true),
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS', true),
        'sql_transaction' => env('SENTRY_BREADCRUMBS_SQL_TRANSACTION', true),
        'redis' => env('SENTRY_BREADCRUMBS_REDIS', true),
        'guzzle' => env('SENTRY_BREADCRUMBS_GUZZLE', true),
        'logs' => env('SENTRY_BREADCRUMBS_LOGS', true),
    ],

    'integrations' => [
    ],

    'ignore_exceptions' => [
        Hyperf\Validation\ValidationException::class,
    ],

    'ignore_transactions' => [
        'GET /health',
    ],

    'ignore_commands' => [
        'crontab:run',
        'gen:*',
        'migrate*',
        'tinker',
        'vendor:publish',
    ],

    // Performance monitoring specific configuration
    'tracing' => [
        'enable' => [
            'amqp' => env('SENTRY_TRACING_ENABLE_AMQP', true),
            'async_queue' => env('SENTRY_TRACING_ENABLE_ASYNC_QUEUE', true),
            'command' => env('SENTRY_TRACING_ENABLE_COMMAND', true),
            'crontab' => env('SENTRY_TRACING_ENABLE_CRONTAB', true),
            'kafka' => env('SENTRY_TRACING_ENABLE_KAFKA', true),
            'missing_routes' => env('SENTRY_TRACING_ENABLE_MISSING_ROUTES', true),
            'request' => env('SENTRY_TRACING_ENABLE_REQUEST', true),
        ],
        'spans' => [
            'cache' => env('SENTRY_TRACING_SPANS_CACHE', true),
            'coroutine' => env('SENTRY_TRACING_SPANS_COROUTINE', true),
            'db' => env('SENTRY_TRACING_SPANS_DB', true),
            'elasticsearch' => env('SENTRY_TRACING_SPANS_ELASTICSEARCH', true),
            'guzzle' => env('SENTRY_TRACING_SPANS_GUZZLE', true),
            'rpc' => env('SENTRY_TRACING_SPANS_RPC', true),
            'grpc' => env('SENTRY_TRACING_SPANS_GRPC', true),
            'redis' => env('SENTRY_TRACING_SPANS_REDIS', true),
            'sql_queries' => env('SENTRY_TRACING_SPANS_SQL_QUERIES', true),
        ],
        'extra_tags' => [
            'exception.stack_trace' => true,
            'amqp.result' => true,
            'annotation.result' => true,
            'db.result' => true,
            'elasticsearch.result' => true,
            'response.body' => true,
            'redis.result' => true,
            'rpc.result' => true,
        ],
    ],

    'crons' => [
        'enable' => env('SENTRY_CRONS_ENABLE', true),
        'checkin_margin' => (int) env('SENTRY_CRONS_CHECKIN_MARGIN', 5),
        'max_runtime' => (int) env('SENTRY_CRONS_MAX_RUNTIME', 15),
        'timezone' => env('SENTRY_CRONS_TIMEZONE', date_default_timezone_get()),
    ],

    'http_timeout' => (float) env('SENTRY_HTTP_TIMEOUT', 2.0),
    'http_chanel_size' => (int) env('SENTRY_HTTP_CHANEL_SIZE', 65535),
    'http_concurrent_limit' => (int) env('SENTRY_HTTP_CONCURRENT_LIMIT', 100),
];
