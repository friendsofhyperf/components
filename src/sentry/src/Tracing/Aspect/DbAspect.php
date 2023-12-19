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
use Hyperf\DB\DB;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\SpanStatus;
use Throwable;

/**
 * @property string $poolName
 */
class DbAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        DB::class . '::__call',
    ];

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnable('db')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $span = $this->startSpan(
            'Db::' . $arguments['name'],
            $proceedingJoinPoint->className . '::' . $arguments['name'] . '()'
        );
        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $data = [];

        if ($this->tagManager->has('db.coroutine.id')) {
            $data[$this->tagManager->get('db.coroutine.id')] = Coroutine::id();
        }

        if ($this->tagManager->has('db.query')) {
            $data[$this->tagManager->get('db.query')] = json_encode($arguments['arguments'], JSON_UNESCAPED_UNICODE);
        }

        if ($this->tagManager->has('db.pool')) {
            $poolName = (fn () => $this->poolName)->call($proceedingJoinPoint->getInstance());
            /** @var \Hyperf\Pool\Pool $pool */
            $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
            $data[$this->tagManager->get('db.pool')] = [
                'name' => $poolName,
                'max' => $pool->getOption()->getMaxConnections(),
                'max_idle_time' => $pool->getOption()->getMaxIdleTime(),
                'idle' => $pool->getConnectionsInChannel(),
                'using' => $pool->getCurrentConnections(),
            ];
        }

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->has('db.result')) {
                $data[$this->tagManager->get('db.result')] = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('db.exception.stack_trace')) {
                $data[$this->tagManager->get('db.exception.stack_trace')] = (string) $exception;
            }

            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish();
        }

        return $result;
    }
}
