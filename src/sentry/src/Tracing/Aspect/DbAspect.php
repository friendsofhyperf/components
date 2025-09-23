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
use FriendsOfHyperf\Sentry\Util\SqlParser;
use Hyperf\Coroutine\Coroutine;
use Hyperf\DB\DB;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\Container\ContainerInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

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
        protected Switcher $switcher
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingSpanEnabled('db')) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $poolName = (fn () => $this->poolName)->call($proceedingJoinPoint->getInstance());
        /** @var \Hyperf\Pool\Pool $pool */
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        $operation = $arguments['name'];
        $database = '';
        $driver = 'unknown';
        $table = '';
        if ($pool instanceof \Hyperf\DB\Pool\Pool) {
            $config = $pool->getConfig();
            $database = $config['database'] ?? '';
            $driver = $config['driver'] ?? 'unknown';
        }

        $sql = $arguments['arguments']['query'] ?? '';
        $sqlParse = SqlParser::parse($sql);
        $table = $sqlParse['table'];
        $operation = $sqlParse['operation'];
        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => $driver,
            'db.name' => $database,
            'db.collection.name' => $table,
            'db.operation.name' => $operation,
            'db.pool.name' => $poolName,
            'db.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.pool.idle' => $pool->getConnectionsInChannel(),
            'db.pool.using' => $pool->getCurrentConnections(),
            // 'server.host' => '',
            // 'server.port' => '',
        ];

        if ($this->switcher->isTracingExtraTagEnabled('db.sql.bindings', true)) {
            $data['db.sql.bindings'] = $arguments['arguments']['bindings'] ?? [];
            foreach ($arguments['arguments']['bindings'] as $key => $value) {
                $data['db.parameter.' . $key] = $value;
            }
        }

        return $this->trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                $result = $proceedingJoinPoint->process();
                if ($this->switcher->isTracingExtraTagEnabled('db.result')) {
                    $scope->getSpan()?->setData([
                        'db.result' => json_encode($result, JSON_UNESCAPED_UNICODE),
                    ]);
                }
                return $result;
            },
            SpanContext::make()
                ->setOp('db.sql.query')
                ->setDescription($sql)
                ->setOrigin('auto.db')
                ->setData($data)
        );
    }
}
