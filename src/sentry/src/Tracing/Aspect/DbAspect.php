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
        $op = match ($arguments['name']) {
            'beginTransaction' => 'db.transaction',
            'commit' => 'db.transaction',
            'rollback' => 'db.transaction',
            'insert' => 'db.sql.execute',
            'execute' => 'db.sql.execute',
            'query' => 'db.sql.query',
            'fetch' => 'db.sql.query',
            default => 'db.query',
        };
        // TODO 规则: opeate dbName.tableName
        $description = sprintf('%s::%s()', $proceedingJoinPoint->className, $arguments['name']);
        $span = $this->startSpan($op, $description);

        if (! $span) {
            return $proceedingJoinPoint->process();
        }

        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => 'mysql', // todo get driver name
            'db.name' => '', // todo get database name
            'db.collection.name' => '', // todo get table name
            'db.operation.name' => '', // todo get operation name
        ];

        $poolName = (fn () => $this->poolName)->call($proceedingJoinPoint->getInstance());
        /** @var \Hyperf\Pool\Pool $pool */
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        $data += [
            'db.pool.name' => $poolName,
            'db.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.pool.idle' => $pool->getConnectionsInChannel(),
            'db.pool.using' => $pool->getCurrentConnections(),
        ];

        foreach ($arguments['arguments']['bindings'] as $key => $value) {
            $data['db.parameter.' . $key] = $value;
        }

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->isEnable('db.result')) {
                $data['db.result'] = json_encode($result, JSON_UNESCAPED_UNICODE);
            }
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->isEnable('exception.stack_trace')) {
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
