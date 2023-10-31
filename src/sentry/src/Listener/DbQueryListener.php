<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\Breadcrumb;

class DbQueryListener implements ListenerInterface
{
    public function __construct(protected Switcher $switcher)
    {
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
            TransactionBeginning::class,
            TransactionCommitted::class,
            TransactionRolledBack::class,
        ];
    }

    /**
     * @param QueryExecuted|TransactionBeginning|TransactionCommitted|TransactionRolledBack $event
     */
    public function process(object $event): void
    {
        match (true) {
            $event instanceof QueryExecuted => $this->queryExecutedHandler($event),
            $event instanceof TransactionBeginning => $this->transactionHandler($event),
            $event instanceof TransactionCommitted => $this->transactionHandler($event),
            $event instanceof TransactionRolledBack => $this->transactionHandler($event),
        };
    }

    /**
     * @param object|QueryExecuted $event
     */
    protected function queryExecutedHandler(object $event): void
    {
        if (! $this->switcher->isBreadcrumbEnable('sql_queries')) {
            return;
        }

        $data = ['connectionName' => $event->connectionName];

        if ($event->time !== null) {
            $data['executionTimeMs'] = $event->time;
        }

        if ($this->switcher->isBreadcrumbEnable('sql_bindings')) {
            $data['bindings'] = $event->bindings;
        }

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.query',
            $event->sql,
            $data
        ));
    }

    /**
     * @param ConnectionEvent|object $event
     */
    protected function transactionHandler(object $event): void
    {
        if (! $this->switcher->isBreadcrumbEnable('sql_transaction', false)) {
            return;
        }

        $data = [
            'connectionName' => $event->connectionName,
        ];

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.transaction',
            $event::class,
            $data
        ));
    }
}
