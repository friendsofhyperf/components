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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
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
 */
class RedisAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('redis')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $data = [];

        if ($this->tagManager->has('coroutine.id')) {
            $data[$this->tagManager->get('coroutine.id')] = Coroutine::id();
        }

        if ($this->tagManager->has('redis.pool')) {
            $poolName = (fn () => $this->poolName)->call($proceedingJoinPoint->getInstance());
            $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
            $data[$this->tagManager->get('redis.pool')] = [
                'name' => $poolName,
                'max' => $pool->getOption()->getMaxConnections(),
                'max_idle_time' => $pool->getOption()->getMaxIdleTime(),
                'idle' => $pool->getConnectionsInChannel(),
                'using' => $pool->getCurrentConnections(),
            ];
        }

        if ($this->tagManager->has('redis.arguments')) {
            $data[$this->tagManager->get('redis.arguments')] = $arguments['arguments'];
        }

        $span = $this->startSpan(
            sprintf('redis.%s', $arguments['name']),
            sprintf('%s::%s()', $proceedingJoinPoint->className, $arguments['name'])
        );

        try {
            $result = $proceedingJoinPoint->process();

            if (! $span) {
                return $result;
            }

            if ($this->tagManager->has('redis.result')) {
                $data[$this->tagManager->get('redis.result')] = $result;
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('redis.exception.stack_trace')) {
                $data[$this->tagManager->get('redis.exception.stack_trace')] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();
        }

        return $result;
    }
}
