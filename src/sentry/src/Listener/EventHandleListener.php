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
use Hyperf\Amqp\Event as AmqpEvent;
use Hyperf\AsyncQueue\Event as AsyncQueueEvent;
use Hyperf\Command\Event as CommandEvent;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Event as CrontabEvent;
use Hyperf\Database\Events as DbEvent;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Event as HttpEvent;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\Kafka\Event as KafkaEvent;
use Hyperf\Redis\Event as RedisEvent;
use Hyperf\RpcServer\Event as RpcEvent;
use Hyperf\RpcServer\Server as RpcServer;
use Hyperf\Server\Event;
use Psr\Container\ContainerInterface;
use Redis;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use Throwable;

class EventHandleListener implements ListenerInterface
{
    public const HUB = 'sentry.context.hub';

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected ConfigInterface $config,
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        return [
            // Framework events
            BootApplication::class,

            // Database events
            DbEvent\QueryExecuted::class,
            DbEvent\TransactionBeginning::class,
            DbEvent\TransactionCommitted::class,
            DbEvent\TransactionRolledBack::class,

            // Redis events
            RedisEvent\CommandExecuted::class,

            // Request events
            HttpEvent\RequestReceived::class,
            HttpEvent\RequestTerminated::class,
            RpcEvent\RequestReceived::class,
            RpcEvent\RequestTerminated::class,

            // Command events
            CommandEvent\BeforeHandle::class,
            CommandEvent\FailToHandle::class,
            CommandEvent\AfterExecute::class,

            // Async Queue events
            AsyncQueueEvent\BeforeHandle::class,
            AsyncQueueEvent\AfterHandle::class,
            AsyncQueueEvent\RetryHandle::class,
            AsyncQueueEvent\FailedHandle::class,

            // Crontab events
            CrontabEvent\BeforeExecute::class,
            CrontabEvent\AfterExecute::class,
            CrontabEvent\FailToExecute::class,

            // AMQP events
            AmqpEvent\BeforeConsume::class,
            AmqpEvent\AfterConsume::class,
            AmqpEvent\FailToConsume::class,

            // Kafka events
            KafkaEvent\BeforeConsume::class,
            KafkaEvent\FailToConsume::class,
            KafkaEvent\AfterConsume::class,
        ];
    }

    public function process(object $event): void
    {
        match ($event::class) {
            // Boot application events
            BootApplication::class => $this->handleBootApplication($event),

            // Database events
            DbEvent\QueryExecuted::class => $this->handleDbQueryExecuted($event),
            DbEvent\TransactionBeginning::class,
            DbEvent\TransactionCommitted::class,
            DbEvent\TransactionRolledBack::class => $this->handleDbTransaction($event),

            // Redis events
            RedisEvent\CommandExecuted::class => $this->handleRedisCommandExecuted($event),

            // Request events
            HttpEvent\RequestReceived::class,
            RpcEvent\RequestReceived::class => $this->handleRequestReceived($event),
            HttpEvent\RequestTerminated::class,
            RpcEvent\RequestTerminated::class => $this->handleRequestTerminated($event),

            // Command events
            CommandEvent\BeforeHandle::class => $this->handleCommandStarting($event),
            CommandEvent\FailToHandle::class => $this->handleCommandFailed($event),
            CommandEvent\AfterExecute::class => $this->handleCommandFinished($event),

            // Async Queue events
            AsyncQueueEvent\BeforeHandle::class => $this->handleAsyncQueueJobProcessing($event),
            AsyncQueueEvent\AfterHandle::class => $this->handleAsyncQueueJobProcessed($event),
            AsyncQueueEvent\RetryHandle::class,
            AsyncQueueEvent\FailedHandle::class => $this->handleAsyncQueueJobRetryOrFailed($event),

            // Crontab events
            CrontabEvent\BeforeExecute::class => $this->handleCrontabTaskStarting($event),
            CrontabEvent\AfterExecute::class => $this->handleCrontabTaskFinished($event),
            CrontabEvent\FailToExecute::class => $this->handleCrontabTaskFailed($event),

            // AMQP events
            AmqpEvent\BeforeConsume::class => $this->handleAmqpMessageProcessing($event),
            AmqpEvent\AfterConsume::class => $this->handleAmqpMessageProcessed($event),
            AmqpEvent\FailToConsume::class => $this->handleAmqpMessageFailed($event),

            // Kafka events
            KafkaEvent\BeforeConsume::class => $this->handleKafkaMessageProcessing($event),
            KafkaEvent\AfterConsume::class => $this->handleKafkaMessageProcessed($event),
            KafkaEvent\FailToConsume::class => $this->handleKafkaMessageFailed($event),

            default => null,
        };
    }

    protected function captureException(?Throwable $throwable): void
    {
        if (! $throwable) {
            return;
        }

        $hub = SentrySdk::getCurrentHub();

        try {
            $hub->captureException($throwable);
        } catch (Throwable $e) {
            $this->logger->error((string) $e);
        } finally {
            $hub->getClient()?->flush();
        }
    }

    protected function setupSentrySdk(): void
    {
        Context::getOrSet(static::HUB, fn () => SentrySdk::init());
    }

    protected function flushEvents(): void
    {
        try {
            Integration::flushEvents();
        } catch (Throwable $e) {
            $this->logger->error((string) $e);
        }
    }

    /**
     * @param BootApplication $event
     */
    protected function handleBootApplication(object $event): void
    {
        $this->setupRequestLifecycle();
        $this->setupRedisEventEnable();
    }

    protected function setupRequestLifecycle(): void
    {
        $keys = [
            'sentry.enable.amqp',
            'sentry.enable.async_queue',
            'sentry.enable.command',
            'sentry.enable.crontab',
            'sentry.enable.kafka',
            'sentry.enable.request',
            'sentry.breadcrumbs.cache',
            'sentry.breadcrumbs.sql_queries',
            'sentry.breadcrumbs.sql_bindings',
            'sentry.breadcrumbs.sql_transaction',
            'sentry.breadcrumbs.redis',
            'sentry.breadcrumbs.guzzle',
            'sentry.breadcrumbs.logs',
            'sentry.enable_tracing',
            'sentry.tracing.enable.amqp',
            'sentry.tracing.enable.async_queue',
            'sentry.tracing.enable.cache',
            'sentry.tracing.enable.command',
            'sentry.tracing.enable.crontab',
            'sentry.tracing.enable.kafka',
            'sentry.tracing.enable.request',
            'sentry.tracing.spans.cache',
            'sentry.tracing.spans.coroutine',
            'sentry.tracing.spans.db',
            'sentry.tracing.spans.elasticsearch',
            'sentry.tracing.spans.guzzle',
            'sentry.tracing.spans.rpc',
            'sentry.tracing.spans.redis',
            'sentry.tracing.spans.sql_queries',
        ];

        foreach ($keys as $key) {
            if (! $this->config->has($key)) {
                $this->config->set($key, true);
            }
        }

        if (
            ! $this->switcher->isEnable('request')
            && ! $this->switcher->isTracingEnable('request')
        ) {
            return;
        }

        $servers = $this->config->get('server.servers', []);

        foreach ($servers as &$server) {
            $callbacks = $server['callbacks'] ?? [];
            $handler = $callbacks[Event::ON_REQUEST][0] ?? $callbacks[Event::ON_RECEIVE][0] ?? null;

            if (! $handler) {
                continue;
            }

            if (
                is_a($handler, HttpServer::class, true)
                || is_a($handler, RpcServer::class, true)
            ) {
                $server['options'] ??= [];
                $server['options']['enable_request_lifecycle'] = true;
            }
        }

        $this->config->set('server.servers', $servers);
    }

    protected function setupRedisEventEnable(): void
    {
        if (! $this->config->has('redis')) {
            return;
        }

        foreach ($this->config->get('redis', []) as $pool => $_) {
            $this->config->set("redis.{$pool}.event.enable", true);
        }
    }

    /**
     * @param DbEvent\QueryExecuted $event
     */
    protected function handleDbQueryExecuted(object $event): void
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
     * @param DbEvent\TransactionBeginning|DbEvent\TransactionCommitted|DbEvent\TransactionRolledBack $event
     */
    protected function handleDbTransaction(object $event): void
    {
        if (! $this->switcher->isBreadcrumbEnable('sql_transaction')) {
            return;
        }

        $data = ['connectionName' => $event->connectionName];

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'sql.transaction',
            $event::class,
            $data
        ));
    }

    /**
     * @param RedisEvent\CommandExecuted $event
     */
    protected function handleRedisCommandExecuted(object $event): void
    {
        if (! $this->switcher->isBreadcrumbEnable('redis')) {
            return;
        }

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'redis',
            $event->command,
            [
                'arguments' => $event->parameters,
                'result' => $event->result,
                'duration' => $event->time * 1000,
            ]
        ));
    }

    /**
     * @param HttpEvent\RequestReceived|RpcEvent\RequestReceived $event
     */
    protected function handleRequestReceived(object $event): void
    {
        if (! $this->switcher->isEnable('request')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param HttpEvent\RequestTerminated|RpcEvent\RequestTerminated $event
     */
    protected function handleRequestTerminated(object $event): void
    {
        if (! $this->switcher->isEnable('request')) {
            return;
        }

        $this->captureException($event->exception);
        $this->flushEvents();
    }

    /**
     * @param CommandEvent\BeforeHandle $event
     */
    protected function handleCommandStarting(object $event): void
    {
        if (! $this->switcher->isEnable('command')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param CommandEvent\FailToHandle $event
     */
    protected function handleCommandFailed(object $event): void
    {
        if (! $this->switcher->isEnable('command')) {
            return;
        }

        $this->captureException($event->getThrowable());
        $this->flushEvents();
    }

    /**
     * @param CommandEvent\AfterExecute $event
     */
    protected function handleCommandFinished(object $event): void
    {
        if (! $this->switcher->isEnable('command')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param AsyncQueueEvent\BeforeHandle $event
     */
    protected function handleAsyncQueueJobProcessing(object $event): void
    {
        if (! $this->switcher->isEnable('async_queue')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param AsyncQueueEvent\AfterHandle $event
     */
    protected function handleAsyncQueueJobProcessed(object $event): void
    {
        if (! $this->switcher->isEnable('async_queue')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param AsyncQueueEvent\RetryHandle|AsyncQueueEvent\FailedHandle $event
     */
    protected function handleAsyncQueueJobRetryOrFailed(object $event): void
    {
        if (! $this->switcher->isEnable('async_queue')) {
            return;
        }

        $this->captureException($event->getThrowable());
        $this->flushEvents();
    }

    /**
     * @param CrontabEvent\BeforeExecute $event
     */
    protected function handleCrontabTaskStarting(object $event): void
    {
        if (! $this->switcher->isEnable('crontab')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param CrontabEvent\AfterExecute $event
     */
    protected function handleCrontabTaskFinished(object $event): void
    {
        if (! $this->switcher->isEnable('crontab')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param CrontabEvent\FailToExecute $event
     */
    protected function handleCrontabTaskFailed(object $event): void
    {
        if (! $this->switcher->isEnable('crontab')) {
            return;
        }

        $this->captureException($event->throwable);
        $this->flushEvents();
    }

    /**
     * @param AmqpEvent\BeforeConsume $event
     */
    protected function handleAmqpMessageProcessing(object $event): void
    {
        if (! $this->switcher->isEnable('amqp')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param AmqpEvent\AfterConsume $event
     */
    protected function handleAmqpMessageProcessed(object $event): void
    {
        if (! $this->switcher->isEnable('amqp')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param AmqpEvent\FailToConsume $event
     */
    protected function handleAmqpMessageFailed(object $event): void
    {
        if (! $this->switcher->isEnable('amqp')) {
            return;
        }

        $this->captureException($event->getThrowable());
        $this->flushEvents();
    }

    /**
     * @param KafkaEvent\BeforeConsume $event
     */
    protected function handleKafkaMessageProcessing(object $event): void
    {
        if (! $this->switcher->isEnable('kafka')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param KafkaEvent\AfterConsume $event
     */
    protected function handleKafkaMessageProcessed(object $event): void
    {
        if (! $this->switcher->isEnable('kafka')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param KafkaEvent\FailToConsume $event
     */
    protected function handleKafkaMessageFailed(object $event): void
    {
        if (! $this->switcher->isEnable('kafka')) {
            return;
        }

        $this->captureException($event->getThrowable());
        $this->flushEvents();
    }
}
