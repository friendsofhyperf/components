<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
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
                '/test/foo' => 'TEST_FOO',
                '/test/bar' => 'TEST_BAR',
            ],
            'watches' => [
                '/test/foo',
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
                'mysql' => ['charset' => 'DB_CHARSET'],
                'redis' => ['port' => 'REDIS_PORT'],
            ],
            'watches' => [
                'test' => [
                    'tenant' => 'framework',
                    'data_id' => 'test',
                    'group' => 'DEFAULT_GROUP',
                    'type' => 'text',
                ],
            ],
        ],
    ],

    'env_path' => BASE_PATH . '/.env',

    'interval' => 1,
];
