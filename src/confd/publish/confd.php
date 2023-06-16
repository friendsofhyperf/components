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
    'default' => env('CONFD_DRIVER', 'etcd'),

    'drivers' => [
        'etcd' => [
            'driver' => \FriendsOfHyperf\Confd\Driver\Etcd::class,
            'client' => [
                'uri' => env('ETCD_URI', ''),
                'version' => 'v3beta',
                'options' => ['timeout' => 10],
            ],
            'namespace' => '/test',
            'mapping' => [
                // etcd key => env key
                '/mysql/host' => 'DB_HOST',
                '/mysql/port' => 'DB_PORT',
            ],
        ],
        'nacos' => [
            'driver' => \FriendsOfHyperf\Confd\Driver\Nacos::class,
            'client' => [
                'host' => '127.0.0.1',
                'port' => 8848,
                'username' => 'nacos',
                'password' => 'nacos',
                'guzzle' => [
                    'config' => ['timeout' => 3, 'connect_timeout' => 1],
                ],
            ],
            'listener_config' => [
                'mysql' => [
                    'tenant' => 'framework',
                    'data_id' => 'mysql',
                    'group' => 'DEFAULT_GROUP',
                    'type' => 'json',
                ],
            ],
            'mapping' => [
                // nacos path => env key
                'mysql.host' => 'DB_HOST',
                'mysql.charset' => 'DB_CHARSET',
                'redis.port' => 'REDIS_PORT',
            ],
        ],
    ],

    'env_path' => BASE_PATH . '/.env',

    'interval' => 1,

    'watches' => [
        'DB_HOST',
    ],
];
