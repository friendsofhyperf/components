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
use FriendsOfHyperf\Sentry\Util\SqlParser;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Event\Contract\ListenerInterface;
use Psr\Container\ContainerInterface;
use Sentry\SentrySdk;

class TracingDbQueryListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher
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
        if (! SentrySdk::getCurrentHub()->getSpan()) {
            return;
        }

        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => $event->connection->getDriverName(),
            'db.name' => $event->connection->getDatabaseName(),
        ];

        $sqlParse = SqlParser::parse($event->sql);
        if (! empty($sqlParse['operation'])) {
            $data['db.operation.name'] = $sqlParse['operation'];
        }
        if (! empty($sqlParse['table'])) {
            $data['db.collection.name'] = $sqlParse['table'];
        }
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
            // 'server.host' => $event->connection->getConfig('host') ?? '',
            // 'server.port' => $event->connection->getConfig('port') ?? '',
            'db.sql.bindings' => $event->bindings,
        ];

        $startTimestamp = microtime(true) - $event->time / 1000;

        // rule: operate db.table
        // $op = sprintf(
        //     '%s%s',
        //     $sqlParse['operation'] ? $sqlParse['operation'] . ' ' : '',
        //     implode('.', array_filter([$event->connection->getDatabaseName(), $sqlParse['table']]))
        // );
        $op = 'db.sql.query';
        $description = $event->sql;

        // Already check in the previous context
        $this->startSpan($op, $description)
            ->setOrigin('auto.db')
            ->setData($data)
            ->setStartTimestamp($startTimestamp)
            ->finish($startTimestamp + $event->time / 1000);
    }
}
