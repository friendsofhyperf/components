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
use FriendsOfHyperf\Sentry\Util\SqlParser;
use Hyperf\DB\Pool\PoolFactory;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use PDO;
use Psr\Container\ContainerInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use WeakMap;

use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Tappable\tap;

/**
 * @property string $poolName
 * @property PDO $connection
 * @property array $config
 */
class DbAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\DB\MySQLConnection::reconnect',
        'Hyperf\DB\DB::getConnection',
        'Hyperf\DB\DB::__call',
    ];

    /**
     * @var WeakMap<\Hyperf\DB\AbstractConnection,array>
     */
    private WeakMap $serverCache;

    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature
    ) {
        $this->serverCache = new WeakMap();
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->feature->isTracingSpanEnabled('db')) {
            return $proceedingJoinPoint->process();
        }

        if ($proceedingJoinPoint->methodName === 'reconnect') {
            return tap($proceedingJoinPoint->process(), function () use ($proceedingJoinPoint) {
                /** @var \Hyperf\DB\AbstractConnection $connection */
                $connection = $proceedingJoinPoint->getInstance();
                $this->serverCache[$connection] = $connection->getConfig();
            });
        }

        if ($proceedingJoinPoint->methodName === 'getConnection') {
            return tap($proceedingJoinPoint->process(), function ($connection) {
                /** @var \Hyperf\DB\AbstractConnection $connection */
                $server = $this->serverCache[$connection] ?? null;
                if ($server !== null) {
                    SentryContext::setDbServerAddress($server['host'] ?? 'localhost');
                    SentryContext::setDbServerPort((int) ($server['port'] ?? 3306));
                }
            });
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $operation = $arguments['name'];
        $database = '';
        $driver = 'unknown';

        /** @var \Hyperf\DB\DB $instance */
        $instance = $proceedingJoinPoint->getInstance();
        $poolName = (fn () => $this->poolName)->call($instance);
        /** @var \Hyperf\Pool\Pool $pool */
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        if ($pool instanceof \Hyperf\DB\Pool\Pool) {
            $config = $pool->getConfig();
            $database = $config['database'] ?? '';
            $driver = $config['driver'] ?? $driver;
        }

        $sql = $arguments['arguments']['query'] ?? '';
        $sqlParse = SqlParser::parse($sql);
        $table = $sqlParse['table'];
        $operation = $sqlParse['operation'];

        $data = [
            'db.system' => $driver,
            'db.name' => $database,
            'db.collection.name' => $table,
            'db.operation.name' => $operation,
            'db.pool.name' => $poolName,
            'db.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.pool.idle' => $pool->getConnectionsInChannel(),
            'db.pool.using' => $pool->getCurrentConnections(),
            'server.host' => SentryContext::getDbServerAddress() ?? 'localhost',
            'server.port' => SentryContext::getDbServerPort() ?? 3306,
        ];

        if ($this->feature->isTracingTagEnabled('db.sql.bindings', true)) {
            $data['db.sql.bindings'] = $arguments['arguments']['bindings'] ?? [];
        }

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                $result = $proceedingJoinPoint->process();
                if ($this->feature->isTracingTagEnabled('db.result')) {
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
