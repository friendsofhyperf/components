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

use FriendsOfHyperf\Sentry\Constants;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Redis;
use RedisCluster;

use function Hyperf\Tappable\tap;

class RedisConnectionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Redis\RedisConnection::__call',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($connection) use ($proceedingJoinPoint) {
            $redisConnection = $proceedingJoinPoint->getInstance();
            $config = (fn () => $this->config ?? [])->call($redisConnection);
            $connection = (fn () => $this->connection ?? null)->call($redisConnection);

            if ($connection instanceof Redis) {
                Context::set(Constants::TRACE_REDIS_SERVER_ADDRESS, $config['host'] ?? 'localhost');
                Context::set(Constants::TRACE_REDIS_SERVER_PORT, $config['port'] ?? 6379);
            }

            if ($connection instanceof RedisCluster) {
                // TODO: support RedisCluster
            }
        });
    }
}
