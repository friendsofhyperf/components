<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Listener;

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;

class TracingDbQueryListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted|TransactionBeginning|TransactionCommitted|TransactionRolledBack $event
     */
    public function process(object $event): void
    {
        match (true) {
            $event instanceof QueryExecuted => $this->queryExecutedHandler($event),
            default => null
        };
    }

    /**
     * @param object|QueryExecuted $event
     */
    protected function queryExecutedHandler(object $event): void
    {
        if (! $this->switcher->isTracingSpanEnable('sql_queries')) {
            return;
        }

        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => $event->connection->getDriverName(),
            'db.name' => $event->connection->getDatabaseName(),
            'db.collection.name' => '', // TODO parse sql to get table name
            'db.operation.name' => '', // todo get operation name
        ];

        foreach ($event->bindings as $key => $value) {
            $data['db.parameter.' . $key] = $value;
        }

        $pool = $this->container->get(PoolFactory::class)->getPool($event->connectionName);
        $data += [
            'db.pool.name' => $event->connectionName,
            'db.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.pool.idle' => $pool->getConnectionsInChannel(),
            'db.pool.using' => $pool->getCurrentConnections(),
        ];

        $startTimestamp = microtime(true) - $event->time / 1000;

        // TODO 规则: opeate dbName.tableName
        $span = $this->startSpan('db.sql.query', $event->sql);

        if (! $span) {
            return;
        }

        $span->setData($data);
        $span->setStartTimestamp($startTimestamp);
        $span->finish($startTimestamp + $event->time / 1000);
    }
}
