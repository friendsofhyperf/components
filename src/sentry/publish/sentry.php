<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'dsn' => env('SENTRY_DSN', ''),

    // capture release as git sha
    // 'release' => trim(exec('git --git-dir ' . (BASE_PATH . '/.git') . ' log --pretty="%h" -n1 HEAD')),

    'environment' => env('APP_ENV'),

    // @see: https://docs.sentry.io/platforms/php/configuration/options/#send-default-pii
    'send_default_pii' => false,

    'breadcrumbs' => [
        'sql_queries' => true,
        'sql_bindings' => true,
        'sql_transaction' => true,
        'redis' => true,
        'guzzle' => true,
        'logs' => true,
    ],

    'integrations' => [],
];
