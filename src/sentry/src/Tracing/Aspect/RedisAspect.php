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
use FriendsOfHyperf\Support\RedisCommand;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Tappable\tap;

/**
 * @deprecated since v3.1, will be removed in v3.2.
 *
 * @property string $poolName
 * @method array getConfig()
 * @property array $config
 */
class RedisAspect extends AbstractAspect
{
    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (
            class_exists(CommandExecuted::class)
            || ! $this->feature->isTracingSpanEnabled('redis')
        ) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $poolName = (fn () => $this->poolName ?? null)->call($proceedingJoinPoint->getInstance());
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        $config = (fn () => $this->config ?? [])->call($pool);
        $data = [
            'db.system' => 'redis',
            'db.statement' => (new RedisCommand($arguments['name'], $arguments['arguments']))->__toString(),
            'db.redis.connection' => $poolName,
            'db.redis.database_index' => $config['db'] ?? 0,
            'db.redis.parameters' => $arguments['arguments'],
            'db.redis.pool.name' => $poolName,
            'db.redis.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.redis.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.redis.pool.idle' => $pool->getConnectionsInChannel(),
            'db.redis.pool.using' => $pool->getCurrentConnections(),
        ];

        $key = $arguments['arguments'][0] ?? '';
        $description = sprintf(
            '%s %s',
            strtoupper($arguments['name'] ?? ''),
            is_array($key) ? implode(',', $key) : $key
        );

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                return tap($proceedingJoinPoint->process(), function ($result) use ($scope) {
                    if ($this->feature->isTracingTagEnabled('redis.result')) {
                        $scope->getSpan()?->setData(['redis.result' => $result]);
                    }
                });
            },
            SpanContext::make()
                ->setOp('db.redis')
                ->setDescription($description)
                ->setOrigin('auto.cache.redis')
                ->setData($data)
        );
    }
}
