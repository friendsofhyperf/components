<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Util\ConnectionContainer;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class RedisConnectionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Redis\RedisConnection::create*',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($connection) use ($proceedingJoinPoint) {
            $instance = $proceedingJoinPoint->getInstance();
            $config = (fn () => $this->config ?? [])->call($instance);

            match ($proceedingJoinPoint->methodName) {
                'createRedis' => ConnectionContainer::set($connection, $config),
                'createRedisCluster' => ConnectionContainer::set($connection, $config['cluster'] ?? []),
                'createRedisSentinel' => ConnectionContainer::set($connection, $config['sentinel'] ?? []),
                default => null,
            };
        });
    }
}
