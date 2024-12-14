<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Telescope\Middleware\Authorize;
use FriendsOfHyperf\Telescope\RecordMode;
use FriendsOfHyperf\Telescope\Storage\DatabaseEntriesRepository;

use function Hyperf\Support\env;

return [
    'enable' => [
        'request' => env('TELESCOPE_ENABLE_REQUEST', false),
        'command' => env('TELESCOPE_ENABLE_COMMAND', false),
        'grpc' => env('TELESCOPE_ENABLE_GRPC', false),
        'log' => env('TELESCOPE_ENABLE_LOG', false),
        'redis' => env('TELESCOPE_ENABLE_REDIS', false),
        'event' => env('TELESCOPE_ENABLE_EVENT', false),
        'exception' => env('TELESCOPE_ENABLE_EXCEPTION', false),
        'job' => env('TELESCOPE_ENABLE_JOB', false),
        'db' => env('TELESCOPE_ENABLE_DB', false),
        'guzzle' => env('TELESCOPE_ENABLE_GUZZLE', false),
        'cache' => env('TELESCOPE_ENABLE_CACHE', false),
        'rpc' => env('TELESCOPE_ENABLE_RPC', false),
        'schedule' => env('TELESCOPE_ENABLE_SCHEDULE', true),
    ],
    'recording' => true,
    'timezone' => env('TELESCOPE_TIMEZONE', 'Asia/Shanghai'),

    'driver' => env('TELESCOPE_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'driver' => DatabaseEntriesRepository::class,
            'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
            'chunk' => (int) env('TELESCOPE_DB_CHUNK', 1000),
        ],
    ],

    'enabled' => env('TELESCOPE_SERVER_ENABLED', false),
    'server' => env('TELESCOPE_SERVER', 'http'),
    'path' => env('TELESCOPE_PATH', '/telescope'),
    'middleware' => [
        Authorize::class,
    ],

    'record_mode' => RecordMode::ASYNC,
    'ignore_logs' => [
    ],
    'only_paths' => [
        // 'api/*'
    ],
    'ignore_paths' => [
        // 'nova-api*',
    ],
    'ignore_commands' => [
        // 'demo:command',
    ],

    'query_slow' => (int) env('TELESCOPE_QUERY_SLOW', 50),
];
