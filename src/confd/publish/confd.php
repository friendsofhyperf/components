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
    ],

    'env_path' => BASE_PATH . '/.env',

    'interval' => 1,
];
