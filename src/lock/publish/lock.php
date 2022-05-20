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
    'default' => [
        'driver' => FriendsOfHyperf\Lock\Driver\RedisLock::class,
        'constructor' => [
            'pool' => 'default',
        ],
    ],
    'file' => [
        'driver' => FriendsOfHyperf\Lock\Driver\FileSystemLock::class,
        'constructor' => [
            'config' => ['prefix' => 'lock:'],
        ],
    ],
    'database' => [
        'driver' => FriendsOfHyperf\Lock\Driver\DatabaseLock::class,
        'constructor' => [
            'pool' => 'default',
            'table' => 'locks',
        ],
    ],
];
