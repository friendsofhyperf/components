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
use FriendsOfHyperf\Support\RedisCommand;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\Pool\PoolFactory;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @deprecated since v3.1, will be removed in v3.2.
 *
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
        if (
            class_exists(CommandExecuted::class)
            || ! $this->switcher->isTracingSpanEnable('redis')
        ) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];

        $poolName = (fn () => $this->poolName ?? null)->call($proceedingJoinPoint->getInstance());
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        $config = (fn () => $this->config ?? [])->call($pool);

        $data = [
            'coroutine.id' => Coroutine::id(),
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
        $span = $this->startSpan(
            op: $op,
            description: $description,
            origin: 'auto.cache.redis',
        )?->setData($data);

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            if ($this->switcher->isTracingExtraTagEnable('redis.result')) {
                $span->setData(['redis.result' => $result]);
            }
        } catch (Throwable $exception) {
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $span->setData(['exception.stack_trace' => (string) $exception]);
            }
            $span?->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => true,
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => $exception->getCode(),
                ]);

            throw $exception;
        } finally {
            $span?->finish();
        }

        return $result;
    }
}
