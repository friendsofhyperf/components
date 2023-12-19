<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Telescope\Telescope;

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
    ],
    'timezone' => env('TELESCOPE_TIMEZONE', 'Asia/Shanghai'),
    'database' => [
        'connection' => env('TELESCOPE_DB_CONNECTION', 'default'),
        'query_slow' => (int) env('TELESCOPE_QUERY_SLOW', 50),
    ],
    'server' => [
        'enable' => env('TELESCOPE_SERVER_ENABLE', false),
        'host' => env('TELESCOPE_SERVER_HOST', '0.0.0.0'),
        'port' => (int) env('TELESCOPE_SERVER_PORT', 9509),
    ],
    'save_mode' => Telescope::SYNC,
    'ignore_logs' => [
    ],
    'path' => env('TELESCOPE_PATH', 'telescope'),
    'only_paths' => [
        // 'api/*'
    ],
    'ignore_paths' => [
        'nova-api*',
    ],
];
