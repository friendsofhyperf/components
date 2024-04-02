<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Commands\Ast\ModelRewriteKeyInfoVisitor;
use Hyperf\Database\Commands\Ast\ModelRewriteSoftDeletesVisitor;
use Hyperf\Database\Commands\Ast\ModelRewriteTimestampsVisitor;
use Hyperf\Session\Handler\RedisHandler;

use function Hyperf\Support\env;

return [
    'default' => [
        'driver' => env('DB_DRIVER', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'hyperf'),
        'port' => env('DB_PORT', 3307),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD'),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_connections' => 1,
            'max_connections' => 20,
            'connect_timeout' => 10.0,
            'wait_timeout' => 3.0,
            'heartbeat' => -1,
            'max_idle_time' => (float) env('DB_MAX_IDLE_TIME', 60),
        ],
        'cache' => [
            'handler' => RedisHandler::class,
            'cache_key' => 'MineAdmin:%s:m:%s:%s:%s',
            'prefix' => 'model-cache',
            'ttl' => 86400 * 7,
            'empty_model_ttl' => 60,
            'load_script' => true,
            'use_default_value' => false,
        ],
        'commands' => [
            'gen:model' => [
                'path' => 'app/Model',
                'force_casts' => true,
                'inheritance' => 'MineModel',
                'uses' => 'Mine\MineModel',
                'with_comments' => true,
                'refresh_fillable' => true,
                'visitors' => [
                    ModelRewriteKeyInfoVisitor::class,
                    ModelRewriteTimestampsVisitor::class,
                    ModelRewriteSoftDeletesVisitor::class,
                ],
            ],
        ],
    ],
];
