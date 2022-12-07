<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Listener;

use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\Contract\ListenerInterface;
use Sentry\Breadcrumb;

class DbQueryListener implements ListenerInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
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
        switch (true) {
            case $event instanceof QueryExecuted: $this->queryExecutedHandler($event);
                break;
            case $event instanceof TransactionBeginning: $this->transactionHandler($event);
                break;
            case $event instanceof TransactionCommitted: $this->transactionHandler($event);
                break;
            case $event instanceof TransactionRolledBack: $this->transactionHandler($event);
                break;
        }
    }

    /**
     * @param object|QueryExecuted $event
     */
    protected function queryExecutedHandler(object $event): void
    {
        if (! $this->config->get('sentry.breadcrumbs.sql_queries', false)) {
            return;
        }

        $data = ['connectionName' => $event->connectionName];

        if ($event->time !== null) {
            $data['executionTimeMs'] = $event->time;
        }

        if ($this->config->get('sentry.breadcrumbs.sql_bindings', false)) {
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
        if (! $this->config->get('sentry.breadcrumbs.sql_transaction', false)) {
            return;
        }

        $data = [
            'connectionName' => $event->connectionName,
        ];

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.transaction',
            get_class($event),
            $data
        ));
    }
}
