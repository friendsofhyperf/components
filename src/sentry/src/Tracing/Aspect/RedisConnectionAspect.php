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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\SentryContext;
use FriendsOfHyperf\Sentry\Util\RedisClusterKeySlot;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Redis;
use RedisCluster;
use WeakMap;

use function Hyperf\Tappable\tap;

class RedisConnectionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Redis\RedisConnection::__call',
    ];

    private WeakMap $slotNodeCache;

    public function __construct(protected Feature $feature)
    {
        $this->slotNodeCache = new WeakMap();
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            if (! $this->feature->isTracingSpanEnabled('redis')) {
                return;
            }

            $redisConnection = $proceedingJoinPoint->getInstance();
            $connection = (fn () => $this->connection ?? null)->call($redisConnection);

            if ($connection instanceof Redis) { // Redis or RedisSentinel
                SentryContext::setRedisServerAddress($connection->getHost());
                SentryContext::setRedisServerPort($connection->getPort());
            }

            if ($connection instanceof RedisCluster) { // RedisCluster
                $arguments = $proceedingJoinPoint->arguments['keys']['arguments'] ?? [];
                $key = $arguments[0] ?? null;
                if (is_string($key)) {
                    $node = $this->getClusterNodeBySlot($connection, $key);
                    if ($node !== null) {
                        SentryContext::setRedisServerAddress($node['host']);
                        SentryContext::setRedisServerPort((int) $node['port']);
                    }
                }
            }
        });
    }

    private function getClusterNodeBySlot(RedisCluster $rc, string $key)
    {
        // $slot = $rc->cluster('CLUSTER', 'KEYSLOT', $key);
        $slot = RedisClusterKeySlot::get($key);
        $slots = ($this->slotNodeCache[$rc] ??= $rc->cluster('CLUSTER', 'SLOTS')); // @phpstan-ignore-line

        foreach ($slots as $range) {
            [$start, $end, $master] = $range;
            if ($slot >= $start && $slot <= $end) {
                // $master = [host, port, nodeId]
                return [
                    'host' => $master[0],
                    'port' => $master[1],
                    'nodeId' => $master[2] ?? null,
                ];
            }
        }

        return null;
    }
}
