<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Cache\Facade;

use Closure;
use DateInterval;
use DateTimeInterface;
use FriendsOfHyperf\Cache\CacheManager;
use FriendsOfHyperf\Cache\Contract\Repository;
use Hyperf\Context\ApplicationContext;

class Cache
{
    /**
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return self::store()->{$name}(...$arguments);
    }

    public static function store(string $name = 'default'): Repository
    {
        return self::getFacadeAccessor()->store($name);
    }

    public static function driver(string $name = 'default'): Repository
    {
        return self::getFacadeAccessor()->driver($name);
    }

    public static function resolve(string $name): Repository
    {
        return self::getFacadeAccessor()->resolve($name);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     */
    public static function add($key, $value, $ttl = null): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Retrieve an item from the cache by key, refreshing it in the background if it is stale.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param array{ 0: DateTimeInterface|DateInterval|int, 1: DateTimeInterface|DateInterval|int } $ttl
     * @param (callable(): TCacheValue) $callback
     * @param null|array{ seconds?: int, owner?: string } $lock
     * @return TCacheValue
     */
    public static function flexible($key, $ttl, $callback, $lock = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Alias for the `clear` method.
     */
    public static function flush(): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function forever($key, $value): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     */
    public static function forget($key): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     */
    public static function has($key): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Determine if an item doesn't exist in the cache.
     *
     * @param string $key
     */
    public static function missing($key): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param array|string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
     */
    public static function put($key, $value, $ttl = null): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    public static function putMany(array $values, $ttl = null): bool
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param int $value
     * @return bool|int
     */
    public static function decrement($key, $value = 1)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param int $value
     * @return bool|int
     */
    public static function increment($key, $value = 1)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @template TCacheValue
     *
     * @param array|string $key
     * @param (Closure(): TCacheValue)|TCacheValue $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public static function get($key, $default = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * @return iterable
     */
    public static function many(array $keys)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @template TCacheValue
     *
     * @param array|string $key
     * @param (Closure(): TCacheValue)|TCacheValue $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public static function pull($key, $default = null)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param null|DateInterval|DateTimeInterface|int $ttl
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public static function remember($key, $ttl, Closure $callback)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public static function rememberForever($key, Closure $callback)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public static function sear($key, Closure $callback)
    {
        return self::__callStatic(__FUNCTION__, func_get_args());
    }

    protected static function getFacadeAccessor(): CacheManager
    {
        return ApplicationContext::getContainer()->get(CacheManager::class);
    }
}
