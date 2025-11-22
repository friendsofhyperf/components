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
use WeakMap;

use function Hyperf\Tappable\tap;

class RedisConnectionAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Redis\RedisConnection::__call',
    ];

    private WeakMap $slotNodeCache;

    public function __construct()
    {
        $this->slotNodeCache = new WeakMap();
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) use ($proceedingJoinPoint) {
            $redisConnection = $proceedingJoinPoint->getInstance();
            $connection = (fn () => $this->connection ?? null)->call($redisConnection);

            if ($connection instanceof Redis) { // Redis or RedisSentinel
                Context::set(Constants::TRACE_REDIS_SERVER_ADDRESS, $connection->getHost());
                Context::set(Constants::TRACE_REDIS_SERVER_PORT, $connection->getPort());
            }

            if ($connection instanceof RedisCluster) { // RedisCluster
                $arguments = $proceedingJoinPoint->arguments['keys']['arguments'] ?? [];
                $key = $arguments[0] ?? null;
                if (is_string($key)) {
                    $slot = $this->getSlotByKey($connection, $key);
                    $slots = $this->getSlots($connection);
                    $node = $this->findNodeBySlot($slots, $slot);
                    if (is_array($node)) {
                        Context::set(Constants::TRACE_REDIS_SERVER_ADDRESS, $node['host']);
                        Context::set(Constants::TRACE_REDIS_SERVER_PORT, $node['port']);
                    }
                }
            }
        });
    }

    private function getSlotByKey(RedisCluster $rc, string $key): int
    {
        return (int) $rc->cluster('CLUSTER', 'KEYSLOT', $key);
    }

    private function getSlots(RedisCluster $rc): array
    {
        $this->slotNodeCache[$rc] ??= $rc->cluster('CLUSTER', 'SLOTS');

        return $this->slotNodeCache[$rc];
    }

    private function findNodeBySlot(array $slots, int $slot): ?array
    {
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
