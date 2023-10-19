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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\Contract\ListenerInterface;

class DbQueryListener implements ListenerInterface
{
    public function __construct(
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

        $data = [];
        if ($this->tagManager->has('sql_queries.coroutine.id')) {
            $data[$this->tagManager->get('sql_queries.coroutine.id')] = $event->sql;
        }
        if ($this->tagManager->has('sql_queries.db.bindings')) {
            $data[$this->tagManager->get('sql_queries.db.bindings')] = $event->bindings;
        }
        if ($this->tagManager->has('sql_queries.db.connection_name')) {
            $data[$this->tagManager->get('sql_queries.db.connection_name')] = $event->connectionName;
        }

        $startTimestamp = microtime(true) - $event->time / 1000;
        SpanContext::create('db.sql.query', $event->sql)
            ->setData($data)
            ->setStartTimestamp($startTimestamp)
            ->finish($startTimestamp + $event->time / 1000);
    }
}
