<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
use function Hyperf\Support\env;

return [
    'connections' => [
        'default' => [
            'enable' => env('TRIGGER_ENABLE', true),

            'host' => env('TRIGGER_HOST', ''),
            'port' => (int) env('TRIGGER_PORT', 3306),
            'user' => env('TRIGGER_USER', ''),
            'password' => env('TRIGGER_PASSWORD', ''),
            'databases_only' => env('TRIGGER_DATABASES_ONLY', '') ? explode(',', env('TRIGGER_DATABASES_ONLY')) : [],
            'tables_only' => env('TRIGGER_TABLES_ONLY', '') ? explode(',', env('TRIGGER_TABLES_ONLY')) : [],
            'heartbeat_period' => (int) env('TRIGGER_HEARTBEAT', 3),

            'server_mutex' => [
                'enable' => true,
                'expires' => 30,
                'keepalive_interval' => 10,
                'retry_interval' => 10,
            ],

            'health_monitor' => [
                'enable' => true,
                'interval' => 30,
            ],

            'snapshot' => [
                'version' => '1.0',
                'expires' => 24 * 3600,
                'interval' => 10,
            ],

            'concurrent' => [
                'limit' => 1000,
            ],

            'consume_timeout' => 600,
        ],
    ],
];
