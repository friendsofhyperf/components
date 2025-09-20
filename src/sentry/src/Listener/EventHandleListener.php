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
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Database\Events\TransactionBeginning;
use Hyperf\Database\Events\TransactionCommitted;
use Hyperf\Database\Events\TransactionRolledBack;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\Kafka\Event as KafkaEvent;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\RpcServer\Event\RequestReceived as RpcRequestReceived;
use Hyperf\RpcServer\Event\RequestTerminated as RpcRequestTerminated;
use Hyperf\RpcServer\Server as RpcServer;
use Hyperf\Server\Event;
use Psr\Container\ContainerInterface;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use Throwable;

class EventHandleListener implements ListenerInterface
{
    public const SETUP = 'sentry.context.setup';

    public function __construct(
        protected ContainerInterface $container,
        protected Switcher $switcher,
        protected ConfigInterface $config
    ) {
    }

    public function listen(): array
    {
        return [
            // Framework events
            BootApplication::class,

            // Database events
            QueryExecuted::class,
            TransactionBeginning::class,
            TransactionCommitted::class,
            TransactionRolledBack::class,

            // Redis events
            CommandExecuted::class,

            // Request events
            RequestReceived::class,
            RequestTerminated::class,
            RpcRequestReceived::class,
            RpcRequestTerminated::class,

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
            QueryExecuted::class => $this->handleDbQuery($event),
            TransactionBeginning::class,
            TransactionCommitted::class,
            TransactionRolledBack::class => $this->handleDbTransaction($event),

            // Redis events
            CommandExecuted::class => $this->handleRedisCommand($event),

            // Request events
            RequestReceived::class,
            RpcRequestReceived::class => $this->handleRequestReceived($event),
            RequestTerminated::class,
            RpcRequestTerminated::class => $this->handleRequestTerminated($event),

            // Command events
            CommandEvent\BeforeHandle::class => $this->handleCommandBefore($event),
            CommandEvent\FailToHandle::class => $this->handleCommandFail($event),
            CommandEvent\AfterExecute::class => $this->handleCommandAfter($event),

            // Async Queue events
            AsyncQueueEvent\BeforeHandle::class => $this->handleAsyncQueueBefore($event),
            AsyncQueueEvent\AfterHandle::class => $this->handleAsyncQueueAfter($event),
            AsyncQueueEvent\RetryHandle::class,
            AsyncQueueEvent\FailedHandle::class => $this->handleAsyncQueueRetryOrFailed($event),

            // Crontab events
            CrontabEvent\BeforeExecute::class => $this->handleCrontabBefore($event),
            CrontabEvent\AfterExecute::class => $this->handleCrontabAfter($event),
            CrontabEvent\FailToExecute::class => $this->handleCrontabFail($event),

            // AMQP events
            AmqpEvent\BeforeConsume::class => $this->handleAmqpBefore($event),
            AmqpEvent\AfterConsume::class => $this->handleAmqpAfter($event),
            AmqpEvent\FailToConsume::class => $this->handleAmqpFail($event),

            // Kafka events
            KafkaEvent\BeforeConsume::class => $this->handleKafkaBefore($event),
            KafkaEvent\AfterConsume::class => $this->handleKafkaAfter($event),
            KafkaEvent\FailToConsume::class => $this->handleKafkaFail($event),

            default => null,
        };
    }

    protected function captureException($throwable): void
    {
        if (! $throwable instanceof Throwable) {
            return;
        }

        $hub = SentrySdk::getCurrentHub();

        try {
            $hub->captureException($throwable);
        } catch (Throwable $e) {
            $this->container->get(StdoutLoggerInterface::class)->error((string) $e);
        } finally {
            $hub->getClient()?->flush();
        }
    }

    protected function setupSentrySdk(): void
    {
        Context::getOrSet(static::SETUP, fn () => SentrySdk::init());
    }

    protected function flushEvents(): void
    {
        try {
            Integration::flushEvents();
        } catch (Throwable $e) {
            $this->container->get(StdoutLoggerInterface::class)->error((string) $e);
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
     * @param QueryExecuted $event
     */
    protected function handleDbQuery(object $event): void
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
     * @param TransactionBeginning|TransactionCommitted|TransactionRolledBack $event
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
     * @param CommandExecuted $event
     */
    protected function handleRedisCommand(object $event): void
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
     * @param RequestReceived|RpcRequestReceived $event
     */
    protected function handleRequestReceived(object $event): void
    {
        if (! $this->switcher->isEnable('request')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param RequestTerminated|RpcRequestTerminated $event
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
    protected function handleCommandBefore(object $event): void
    {
        if (! $this->switcher->isEnable('command')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param CommandEvent\FailToHandle $event
     */
    protected function handleCommandFail(object $event): void
    {
        if (! $this->switcher->isEnable('command')) {
            return;
        }

        $this->captureException($event->getThrowable());
    }

    /**
     * @param CommandEvent\AfterExecute $event
     */
    protected function handleCommandAfter(object $event): void
    {
        if (! $this->switcher->isEnable('command')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param AsyncQueueEvent\BeforeHandle $event
     */
    protected function handleAsyncQueueBefore(object $event): void
    {
        if (! $this->switcher->isEnable('async_queue')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param AsyncQueueEvent\AfterHandle $event
     */
    protected function handleAsyncQueueAfter(object $event): void
    {
        if (! $this->switcher->isEnable('async_queue')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param AsyncQueueEvent\RetryHandle|AsyncQueueEvent\FailedHandle $event
     */
    protected function handleAsyncQueueRetryOrFailed(object $event): void
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
    protected function handleCrontabBefore(object $event): void
    {
        if (! $this->switcher->isEnable('crontab')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param CrontabEvent\AfterExecute $event
     */
    protected function handleCrontabAfter(object $event): void
    {
        if (! $this->switcher->isEnable('crontab')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param CrontabEvent\FailToExecute $event
     */
    protected function handleCrontabFail(object $event): void
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
    protected function handleAmqpBefore(object $event): void
    {
        if (! $this->switcher->isEnable('amqp')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param AmqpEvent\AfterConsume $event
     */
    protected function handleAmqpAfter(object $event): void
    {
        if (! $this->switcher->isEnable('amqp')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param AmqpEvent\FailToConsume $event
     */
    protected function handleAmqpFail(object $event): void
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
    protected function handleKafkaBefore(object $event): void
    {
        if (! $this->switcher->isEnable('kafka')) {
            return;
        }

        $this->setupSentrySdk();
    }

    /**
     * @param KafkaEvent\AfterConsume $event
     */
    protected function handleKafkaAfter(object $event): void
    {
        if (! $this->switcher->isEnable('kafka')) {
            return;
        }

        $this->flushEvents();
    }

    /**
     * @param KafkaEvent\FailToConsume $event
     */
    protected function handleKafkaFail(object $event): void
    {
        if (! $this->switcher->isEnable('kafka')) {
            return;
        }

        $this->captureException($event->getThrowable());
        $this->flushEvents();
    }
}
