<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\RateLimit;

use FriendsOfHyperf\RateLimit\Algorithm\FixedWindowRateLimiter;
use FriendsOfHyperf\RateLimit\Algorithm\LeakyBucketRateLimiter;
use FriendsOfHyperf\RateLimit\Algorithm\SlidingWindowRateLimiter;
use FriendsOfHyperf\RateLimit\Algorithm\TokenBucketRateLimiter;
use FriendsOfHyperf\RateLimit\Contract\RateLimiterInterface;
use FriendsOfHyperf\RateLimit\Exception\RateLimitException;
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;

class RateLimiterFactory
{
    protected array $limiters = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function make(string $algorithm = 'fixed_window', ?string $connection = null): RateLimiterInterface
    {
        $key = $algorithm . ':' . ($connection ?? 'default');

        if (isset($this->limiters[$key])) {
            return $this->limiters[$key];
        }

        $redis = $this->getRedis($connection);
        $prefix = $this->getPrefix();

        return $this->limiters[$key] = match ($algorithm) {
            'fixed_window' => new FixedWindowRateLimiter($redis, $prefix),
            'sliding_window' => new SlidingWindowRateLimiter($redis, $prefix),
            'token_bucket' => new TokenBucketRateLimiter($redis, $prefix),
            'leaky_bucket' => new LeakyBucketRateLimiter($redis, $prefix),
            default => throw new RateLimitException("Unsupported rate limiter algorithm: {$algorithm}"),
        };
    }

    protected function getRedis(?string $connection = null): Redis
    {
        return $this->container->get(RedisFactory::class)->get($connection);
    }

    protected function getPrefix(): string
    {
        return 'rate_limit';
    }
}
