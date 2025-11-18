<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit\Algorithm;

use FriendsOfHyperf\RateLimit\Contract\RateLimiterInterface;
use FriendsOfHyperf\RateLimit\Storage\LuaScripts;
use Hyperf\Redis\Redis;

class TokenBucketRateLimiter implements RateLimiterInterface
{
    public function __construct(protected Redis $redis, protected string $prefix = 'rate_limit')
    {
    }

    public function attempt(string $key, int $maxAttempts, int $decay): bool
    {
        $refillRate = $maxAttempts / $decay;

        $result = $this->redis->eval(
            LuaScripts::tokenBucket(),
            [$this->getKey($key), $maxAttempts, $refillRate, 1, microtime(true)],
            1
        );

        return (bool) $result[0];
    }

    public function tooManyAttempts(string $key, int $maxAttempts, int $decay): bool
    {
        return ! $this->attempt($key, $maxAttempts, $decay);
    }

    public function attempts(string $key): int
    {
        $bucket = $this->redis->hGet($this->getKey($key), 'tokens');
        return $bucket ? (int) $bucket : 0;
    }

    public function remaining(string $key, int $maxAttempts): int
    {
        $tokens = $this->attempts($key);
        return max(0, (int) $tokens);
    }

    public function clear(string $key): void
    {
        $this->redis->del($this->getKey($key));
    }

    public function availableIn(string $key): int
    {
        $ttl = $this->redis->ttl($this->getKey($key));
        return (is_int($ttl) && $ttl > 0) ? $ttl : 0;
    }

    protected function getKey(string $key): string
    {
        return $this->prefix . ':token:' . $key;
    }
}
