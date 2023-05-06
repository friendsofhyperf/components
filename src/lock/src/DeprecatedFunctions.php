<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Lock\Driver\LockInterface;
use FriendsOfHyperf\Lock\LockFactory;

if (! function_exists('lock')) {
    /**
     * @deprecated v3.1, use \FriendsOfHyperf\Lock\lock() instead.
     */
    function lock(string $name = null, int $seconds = 0, ?string $owner = null, string $driver = 'default'): LockFactory|LockInterface
    {
        return \FriendsOfHyperf\Lock\lock($name, $seconds, $owner, $driver);
    }
}
