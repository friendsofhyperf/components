<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    'dsn' => env('SENTRY_DSN', ''),

    // capture release as git sha
    // 'release' => trim(exec('git --git-dir ' . (BASE_PATH . '/.git') . ' log --pretty="%h" -n1 HEAD')),

    'environment' => env('APP_ENV', 'production'),

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#send-default-pii
    'send_default_pii' => env('SENTRY_SEND_DEFAULT_PII', false),

    'breadcrumbs' => [
        'sql_queries' => env('SENTRY_BREADCRUMBS_SQL_QUERIES', true),
        'sql_bindings' => env('SENTRY_BREADCRUMBS_SQL_BINDINGS', true),
        'sql_transaction' => env('SENTRY_BREADCRUMBS_SQL_TRANSACTION', true),
        'redis' => env('SENTRY_BREADCRUMBS_REDIS', true),
        'guzzle' => env('SENTRY_BREADCRUMBS_GUZZLE', true),
        'logs' => env('SENTRY_BREADCRUMBS_LOGS', true),
    ],

    'integrations' => [],

    'dont_report' => [
        Hyperf\Validation\ValidationException::class,
    ],
];
