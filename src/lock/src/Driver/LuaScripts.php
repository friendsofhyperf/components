<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Driver;

class LuaScripts
{
    /**
     * Get the Lua script to atomically release a lock.
     * KEYS[1] - The name of the lock
     * ARGV[1] - The owner key of the lock instance trying to release it.
     * @return string
     */
    public static function releaseLock()
    {
        return <<<'LUA'
if redis.call("get",KEYS[1]) == ARGV[1] then
    return redis.call("del",KEYS[1])
else
    return 0
end
LUA;
    }
}
