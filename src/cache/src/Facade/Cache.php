<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache\Facade;

use FriendsOfHyperf\Cache\CacheInterface;
use FriendsOfHyperf\Cache\CacheManager;
use Hyperf\Utils\ApplicationContext;

/**
 * @method static bool add($key, $value, $ttl = null);
 * @method static bool flush();
 * @method static bool forever($key, $value);
 * @method static bool forget($key);
 * @method static bool has($key);
 * @method static bool missing($key);
 * @method static bool put($key, $value, $ttl = null);
 * @method static bool putMany(array $values, $ttl = null);
 * @method static bool|int decrement($key, $value = 1);
 * @method static bool|int increment($key, $value = 1);
 * @method static mixed get($key, $default = null);
 * @method static array many(array $keys);
 * @method static mixed pull(string $key, $default = null);
 * @method static mixed remember($key, $ttl, Closure $callback);
 * @method static mixed rememberForever($key, Closure $callback);
 * @method static mixed sear($key, Closure $callback);
 * @see \FriendsOfHyperf\Cache\Cache
 * @see \FriendsOfHyperf\Cache\CacheInterface
 */
class Cache
{
    /**
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::driver()->{$name}(...$arguments);
    }

    public static function driver(string $name = 'default'): CacheInterface
    {
        return ApplicationContext::getContainer()->get(CacheManager::class)->get($name);
    }
}
