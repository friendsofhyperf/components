<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
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
     * @param null|DateInterval|DateTimeInterface|int $ttl
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
     * @param string $key
     */
    public function has($key): bool;

    /**
     * @param string $key
     */
    public function missing($key): bool;

    /**
     * @param array|string $key
     * @param mixed $value
     * @param null|DateInterval|DateTimeInterface|int $ttl
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
     * @param array|string $key
     * @param null|mixed $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @return iterable
     */
    public function many(array $keys);

    /**
     * @param null|mixed $default
     * @return mixed
     */
    public function pull(string $key, $default = null);

    /**
     * @param DateInterval|DateTimeInterface|int $ttl
     * @param string $key
     * @return mixed
     */
    public function remember($key, $ttl, Closure $callback);

    /**
     * @param string $key
     * @return mixed
     */
    public function rememberForever($key, Closure $callback);

    /**
     * @param string $key
     * @return mixed
     */
    public function sear($key, Closure $callback);
}
