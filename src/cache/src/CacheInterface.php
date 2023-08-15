<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Cache;

use Closure;
use DateInterval;
use DateTimeInterface;

interface CacheInterface
{
    /**
     * @param string $key
     * @param mixed $value
     * @param DateInterval|DateTimeInterface|int|null $ttl
     */
    public function add($key, $value, $ttl = null): bool;

    public function flush(): bool;

    /**
     * @param string $key
     * @param mixed $value
     */
    public function forever($key, $value): bool;

    /**
     * @param string $key
     */
    public function forget($key): bool;

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     */
    public function has($key): bool;

    /**
     * Determine if an item doesn't exist in the cache.
     *
     * @param string $key
     */
    public function missing($key): bool;

    /**
     * @param array|string $key
     * @param mixed $value
     * @param DateInterval|DateTimeInterface|int|null $ttl
     */
    public function put($key, $value, $ttl = null): bool;

    public function putMany(array $values, $ttl = null): bool;

    /**
     * @param string $key
     * @param int $value
     * @return bool|int
     */
    public function decrement($key, $value = 1);

    /**
     * @param string $key
     * @param int $value
     * @return bool|int
     */
    public function increment($key, $value = 1);

    /**
     * Retrieve an item from the cache by key.
     *
     * @template TCacheValue
     *
     * @param array|string $key
     * @param (Closure(): TCacheValue)|TCacheValue $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public function get($key, $default = null);

    /**
     * @return iterable
     */
    public function many(array $keys);

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @template TCacheValue
     *
     * @param array|string $key
     * @param (Closure(): TCacheValue)|TCacheValue $default
     * @return (TCacheValue is null ? mixed : TCacheValue)
     */
    public function pull(string $key, $default = null);

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param DateInterval|DateTimeInterface|int|null $ttl
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public function remember($key, $ttl, Closure $callback);

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public function rememberForever($key, Closure $callback);

    /**
     * Get an item from the cache, or execute the given Closure and store the result forever.
     *
     * @template TCacheValue
     *
     * @param string $key
     * @param Closure(): TCacheValue $callback
     * @return TCacheValue
     */
    public function sear($key, Closure $callback);
}
