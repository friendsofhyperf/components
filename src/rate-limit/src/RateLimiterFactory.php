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
use Hyperf\Redis\Redis;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;

class RateLimiterFactory
{
    /**
     * @var array<string,RateLimiterInterface>
     */
    protected array $limiters = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function make(Algorithm $algorithm = Algorithm::FIXED_WINDOW, ?string $pool = null): RateLimiterInterface
    {
        $key = $algorithm->value . ':' . ($pool ?? 'default');

        if (isset($this->limiters[$key])) {
            return $this->limiters[$key];
        }

        $redis = $this->getRedis($pool);
        $prefix = $this->getPrefix();

        return $this->limiters[$key] = match ($algorithm) {
            Algorithm::FIXED_WINDOW => new FixedWindowRateLimiter($redis, $prefix),
            Algorithm::SLIDING_WINDOW => new SlidingWindowRateLimiter($redis, $prefix),
            Algorithm::TOKEN_BUCKET => new TokenBucketRateLimiter($redis, $prefix),
            Algorithm::LEAKY_BUCKET => new LeakyBucketRateLimiter($redis, $prefix),
        };
    }

    protected function getRedis(?string $pool = null): Redis
    {
        return $this->container->get(RedisFactory::class)->get($pool ?? 'default');
    }

    protected function getPrefix(): string
    {
        return 'rate_limit';
    }
}
