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

use FriendsOfHyperf\Cache\CacheInterface;
use FriendsOfHyperf\Cache\CacheManager;
use Hyperf\Context\ApplicationContext;

/**
 * @template TCacheValue
 * @method static bool add(string $key, mixed $value, \DateInterval|\DateTimeInterface|int|null $ttl = null)
 * @method static bool delete(string $key)
 * @method static bool deleteMultiple(iterable<string> $keys)
 * @method static bool flush()
 * @method static bool forever(string $key, mixed $value)
 * @method static bool forget(string $key)
 * @method static iterable getMultiple(iterable<string> $keys, mixed $default = null)
 * @method static bool has(string $key)
 * @method static bool missing(string $key)
 * @method static bool put(array|string $key, mixed $value, \DateInterval|\DateTimeInterface|int|null $ttl = null)
 * @method static bool putMany(array $values, \DateInterval|\DateTimeInterface|int|null $ttl = null)
 * @method static bool|int decrement(string $key, mixed $value = 1)
 * @method static bool|int increment(string $key, mixed $value = 1)
 * @method static (TCacheValue get(array|string $key, TCacheValue|(\Closure():TCacheValue) $default = null)
 * @method static array many(array $keys)
 * @method static (TCacheValue pull(array|string $key, TCacheValue|(\Closure():TCacheValue) $default = null)
 * @method static TCacheValue remember(string $key, \Closure|\DateInterval|\DateTimeInterface|int|null $ttl, \Closure():TCacheValue $callback)
 * @method static TCacheValue rememberForever(string $key, \Closure():TCacheValue $callback)
 * @method static TCacheValue sear(string $key, \Closure():TCacheValue $callback)
 * @method static bool setMultiple(iterable $values, \DateInterval|int|null $ttl = null)
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
        return self::store()->{$name}(...$arguments);
    }

    public static function store(string $name = 'default'): CacheInterface
    {
        return ApplicationContext::getContainer()->get(CacheManager::class)->store($name);
    }

    public static function driver(string $name = 'default'): CacheInterface
    {
        return self::store($name);
    }
}
