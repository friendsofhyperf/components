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
use FriendsOfHyperf\Sentry\Tracing\SpanContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\Contract\ListenerInterface;

class TracingDbQueryListener implements ListenerInterface
{
    public function __construct(protected Switcher $switcher)
    {
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
        };
    }

    /**
     * @param object|QueryExecuted $event
     */
    protected function queryExecutedHandler(object $event): void
    {
        if (! $this->switcher->isTracingEnable('sql_queries')) {
            return;
        }

        $data = [
            'sql_queries.coroutine.id' => Coroutine::id(),
            'sql_queries.db.query' => $event->sql,
            'sql_queries.db.time' => $event->time,
            'sql_queries.db.bindings' => $event->bindings,
            'sql_queries.db.connection_name' => $event->connectionName,
        ];

        $startTimestamp = microtime(true) - $event->time / 1000;
        SpanContext::create('db.sql.query', $event->sql)
            ->setData($data)
            ->setStartTimestamp($startTimestamp)
            ->finish($startTimestamp + $event->time / 1000);
    }
}
