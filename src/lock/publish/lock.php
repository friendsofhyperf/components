<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
return [
    'default' => [
        'driver' => FriendsOfHyperf\Lock\Driver\RedisLock::class,
        'constructor' => ['pool' => 'default', 'prefix' => 'lock:'],
    ],
    'file' => [
        'driver' => FriendsOfHyperf\Lock\Driver\FileSystemLock::class,
        'constructor' => [
            'config' => ['prefix' => 'lock:'],
        ],
    ],
    'database' => [
        'driver' => FriendsOfHyperf\Lock\Driver\DatabaseLock::class,
        'constructor' => ['pool' => 'default', 'table' => 'locks', 'prefix' => 'lock:'],
    ],
    'co' => [
        'driver' => FriendsOfHyperf\Lock\Driver\CoroutineLock::class,
        'constructor' => ['prefix' => 'lock:'],
    ],
];
