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

        $data = [];

        if ($this->tagManager->has('sql_queries.coroutine.id')) {
            $data[$this->tagManager->get('sql_queries.coroutine.id')] = Coroutine::id();
        }
        if ($this->tagManager->has('sql_queries.db.bindings')) {
            $data[$this->tagManager->get('sql_queries.db.bindings')] = $event->bindings;
        }
        if ($this->tagManager->has('sql_queries.db.connection_name')) {
            $data[$this->tagManager->get('sql_queries.db.connection_name')] = $event->connectionName;
        }
        if ($this->tagManager->has('sql_queries.db.pool')) {
            $pool = $this->container->get(PoolFactory::class)->getPool($event->connectionName);
            $data[$this->tagManager->get('sql_queries.db.pool')] = [
                'name' => $event->connectionName,
                'max' => $pool->getOption()->getMaxConnections(),
                'max_idle_time' => $pool->getOption()->getMaxIdleTime(),
                'idle' => $pool->getConnectionsInChannel(),
                'using' => $pool->getCurrentConnections(),
            ];
        }

        $startTimestamp = microtime(true) - $event->time / 1000;

        $span = $this->startSpan(
            'db.sql.query',
            $event->sql,
        );
        $span->setData($data);
        $span->setStartTimestamp($startTimestamp);
        $span->finish($startTimestamp + $event->time / 1000);
    }
}
