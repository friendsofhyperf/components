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

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @property string $poolName
 * @method array getConfig()
 * @property array $config
 */
class RedisAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('redis')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];

        $poolName = (fn () => $this->poolName ?? null)->call($proceedingJoinPoint->getInstance());
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        $config = (fn () => $this->config ?? [])->call($pool);

        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => 'redis',
            'db.redis.connection' => $poolName,
            'db.redis.database_index' => $config['db'] ?? 0,
            'db.redis.parameters' => $arguments['arguments'],
            // 'db.statement' => strtoupper($arguments['name']) . implode(' ', $arguments['arguments']),
            'db.redis.pool.name' => $poolName,
            'db.redis.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.redis.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.redis.pool.idle' => $pool->getConnectionsInChannel(),
            'db.redis.pool.using' => $pool->getCurrentConnections(),
        ];

        // rule: operation db.table
        // $op = sprintf('%s %s', $arguments['name'], $arguments['arguments']['key'] ?? '');
        // $description = sprintf('%s::%s()', $proceedingJoinPoint->className, $arguments['name']);
        $key = $arguments['arguments'][0] ?? '';
        $op = 'db.redis';
        $description = sprintf(
            '%s %s',
            strtoupper($arguments['name'] ?? ''),
            is_array($key) ? implode(',', $key) : $key
        );
        $span = $this->startSpan($op, $description);

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            if ($this->switcher->isTracingExtraTagEnable('redis.result')) {
                $data['redis.result'] = $result;
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();
        }

        return $result;
    }
}
