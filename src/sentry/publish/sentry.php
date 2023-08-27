<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Sentry\Integration;

use function Hyperf\Support\env;

return [
    'dsn' => env('SENTRY_DSN', ''),

    // The release version of your application
    // Example with dynamic git hash: trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD'))
    'release' => env('SENTRY_RELEASE'),

    // When left empty or `null` the Laravel environment will be used (usually discovered from `APP_ENV` in your `.env`)
    'environment' => env('APP_ENV', 'production'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#sample-rate
    'sample_rate' => env('SENTRY_SAMPLE_RATE') === null ? 1.0 : (float) env('SENTRY_SAMPLE_RATE'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#traces-sample-rate
    'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE') === null ? null : (float) env('SENTRY_TRACES_SAMPLE_RATE'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#profiles-sample-rate
    'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE') === null ? null : (float) env('SENTRY_PROFILES_SAMPLE_RATE'),

    // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#send-default-pii
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    'enable' => [
        'amqp' => env('SENTRY_ENABLE_AMQP', true),
        'async_queue' => env('SENTRY_ENABLE_ASYNC_QUEUE', true),
        'command' => env('SENTRY_ENABLE_COMMAND', true),
        'crontab' => env('SENTRY_ENABLE_CRONTAB', true),
        'kafka' => env('SENTRY_ENABLE_KAFKA', true),
        'request' => env('SENTRY_ENABLE_REQUEST', true),
    ],

    'breadcrumbs' => [
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES', true),
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS', true),
        'sql_transaction' => env('SENTRY_BREADCRUMBS_SQL_TRANSACTION', true),
        'redis' => env('SENTRY_BREADCRUMBS_REDIS', true),
        'guzzle' => env('SENTRY_BREADCRUMBS_GUZZLE', true),
        'logs' => env('SENTRY_BREADCRUMBS_LOGS', true),
    ],

    'integrations' => [
        Integration\TraceIntegration::class,
    ],

    'ignore_exceptions' => [
        Hyperf\Validation\ValidationException::class,
    ],

    'ignore_transactions' => [
        'GET /health',
    ],
];
