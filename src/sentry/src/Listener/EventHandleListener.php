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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Amqp\Event as AmqpEvent;
use Hyperf\AsyncQueue\Event as AsyncQueueEvent;
use Hyperf\Command\Event as CommandEvent;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Crontab\Event as CrontabEvent;
use Hyperf\Database\Events as DbEvent;
use Hyperf\DbConnection\Pool\PoolFactory as DbPoolFactory;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event as HttpEvent;
use Hyperf\Kafka\Event as KafkaEvent;
use Hyperf\Redis\Event as RedisEvent;
use Hyperf\Redis\Pool\PoolFactory as RedisPoolFactory;
use Hyperf\RpcServer\Event as RpcEvent;
use Psr\Container\ContainerInterface;
use Sentry\Breadcrumb;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Throwable;

/**
 * @property InputInterface $input
 * @property int $exitCode
 */
class EventHandleListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected Feature $feature,
        protected ConfigInterface $config,
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function listen(): array
    {
        return [
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

    /**
     * @param DbEvent\QueryExecuted $event
     */
    protected function handleDbQueryExecuted(object $event): void
    {
        if (! $this->feature->isBreadcrumbEnabled('sql_queries')) {
            return;
        }

        $data = ['connectionName' => $event->connectionName];

        if ($event->time !== null) {
            $data['executionTimeMs'] = $event->time;
        }

        if ($this->feature->isBreadcrumbEnabled('sql_bindings')) {
            $data['bindings'] = $event->bindings;
        }

        try {
            $pool = $this->container->get(DbPoolFactory::class)->getPool($event->connectionName);
            $data['pool'] = [
                'max' => $pool->getOption()->getMaxConnections(),
                'waiting' => $pool->getConnectionsInChannel(),
                'use' => $pool->getCurrentConnections(),
            ];
        } catch (Throwable $e) {
            $this->captureException($e);
        }

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'db.sql.query',
            $event->sql,
            $data
        ));
    }

    /**
     * @param DbEvent\TransactionBeginning|DbEvent\TransactionCommitted|DbEvent\TransactionRolledBack $event
     */
    protected function handleDbTransaction(object $event): void
    {
        if (! $this->feature->isBreadcrumbEnabled('sql_transaction')) {
            return;
        }

        $data = ['connectionName' => $event->connectionName];

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'db.sql.transaction',
            $event::class,
            $data
        ));
    }

    /**
     * @param RedisEvent\CommandExecuted $event
     */
    protected function handleRedisCommandExecuted(object $event): void
    {
        if (! $this->feature->isBreadcrumbEnabled('redis')) {
            return;
        }

        $data = [
            'connectionName' => $event->connectionName,
            'arguments' => $event->parameters,
            'result' => $event->result,
            'duration' => $event->time * 1000,
        ];

        try {
            $pool = $this->container->get(RedisPoolFactory::class)->getPool($event->connectionName);
            $data['pool'] = [
                'max' => $pool->getOption()->getMaxConnections(),
                'waiting' => $pool->getConnectionsInChannel(),
                'use' => $pool->getCurrentConnections(),
            ];
        } catch (Throwable $e) {
            $this->captureException($e);
        }

        Integration::addBreadcrumb(new Breadcrumb(
            Breadcrumb::LEVEL_INFO,
            Breadcrumb::TYPE_DEFAULT,
            'redis',
            $event->command,
            $data
        ));
    }

    /**
     * @param HttpEvent\RequestReceived|RpcEvent\RequestReceived $event
     */
    protected function handleRequestReceived(object $event): void
    {
        if (! $this->feature->isEnabled('request')) {
            return;
        }
    }

    /**
     * @param HttpEvent\RequestTerminated|RpcEvent\RequestTerminated $event
     */
    protected function handleRequestTerminated(object $event): void
    {
        if (! $this->feature->isEnabled('request')) {
            return;
        }

        $this->captureException($event->exception);
    }

    /**
     * @param CommandEvent\BeforeHandle $event
     */
    protected function handleCommandStarting(object $event): void
    {
        if (! $this->feature->isEnabled('command')) {
            return;
        }

        Integration::configureScope(static function (Scope $scope) use ($event): void {
            $scope->setTag('command', $event->getCommand()->getName());
        });

        if ($this->feature->isBreadcrumbEnabled('command')) {
            $data = [];
            if ($this->feature->isBreadcrumbEnabled('command_input')) {
                /** @var InputInterface $input */
                $input = (fn () => $this->input)->call($event->getCommand());
                $data['input'] = $this->extractConsoleCommandInput($input);
            }

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'command',
                'Starting command: ' . $event->getCommand()->getName(),
                $data
            ));
        }
    }

    /**
     * @param CommandEvent\AfterExecute $event
     */
    protected function handleCommandFinished(object $event): void
    {
        if (! $this->feature->isEnabled('command')) {
            return;
        }

        if ($this->feature->isBreadcrumbEnabled('command')) {
            /** @var InputInterface $input */
            /** @var int $exitCode */
            [$input, $exitCode] = (function () {
                return [
                    $this->input,
                    $this->exitCode,
                ];
            })->call($event->getCommand());
            $data = ['exit' => $exitCode];

            if ($this->feature->isBreadcrumbEnabled('command_input')) {
                $data['input'] = $this->extractConsoleCommandInput($input);
            }

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'command',
                'Finished command: ' . $event->getCommand()->getName(),
                $data
            ));
        }

        $this->captureException($event->getThrowable());

        Integration::configureScope(static function (Scope $scope): void {
            $scope->removeTag('command');
        });
    }

    /**
     * @param AsyncQueueEvent\BeforeHandle $event
     */
    protected function handleAsyncQueueJobProcessing(object $event): void
    {
        if (! $this->feature->isEnabled('async_queue')) {
            return;
        }

        if ($this->feature->isBreadcrumbEnabled('async_queue')) {
            $job = [
                'job' => $event->getMessage()->job()::class,
                'attempts' => $event->getMessage()->getAttempts(),
            ];

            Integration::addBreadcrumb(new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'queue.job',
                'Processing async_queue job',
                $job
            ));
        }
    }

    /**
     * @param AsyncQueueEvent\AfterHandle $event
     */
    protected function handleAsyncQueueJobProcessed(object $event): void
    {
        if (! $this->feature->isEnabled('async_queue')) {
            return;
        }
    }

    /**
     * @param AsyncQueueEvent\RetryHandle|AsyncQueueEvent\FailedHandle $event
     */
    protected function handleAsyncQueueJobRetryOrFailed(object $event): void
    {
        if (! $this->feature->isEnabled('async_queue')) {
            return;
        }

        $this->captureException($event->getThrowable());
    }

    /**
     * @param CrontabEvent\BeforeExecute $event
     */
    protected function handleCrontabTaskStarting(object $event): void
    {
        if (! $this->feature->isEnabled('crontab')) {
            return;
        }
    }

    /**
     * @param CrontabEvent\AfterExecute $event
     */
    protected function handleCrontabTaskFinished(object $event): void
    {
        if (! $this->feature->isEnabled('crontab')) {
            return;
        }
    }

    /**
     * @param CrontabEvent\FailToExecute $event
     */
    protected function handleCrontabTaskFailed(object $event): void
    {
        if (! $this->feature->isEnabled('crontab')) {
            return;
        }

        $this->captureException($event->throwable);
    }

    /**
     * @param AmqpEvent\BeforeConsume $event
     */
    protected function handleAmqpMessageProcessing(object $event): void
    {
        if (! $this->feature->isEnabled('amqp')) {
            return;
        }
    }

    /**
     * @param AmqpEvent\AfterConsume $event
     */
    protected function handleAmqpMessageProcessed(object $event): void
    {
        if (! $this->feature->isEnabled('amqp')) {
            return;
        }
    }

    /**
     * @param AmqpEvent\FailToConsume $event
     */
    protected function handleAmqpMessageFailed(object $event): void
    {
        if (! $this->feature->isEnabled('amqp')) {
            return;
        }

        $this->captureException($event->getThrowable());
    }

    /**
     * @param KafkaEvent\BeforeConsume $event
     */
    protected function handleKafkaMessageProcessing(object $event): void
    {
        if (! $this->feature->isEnabled('kafka')) {
            return;
        }
    }

    /**
     * @param KafkaEvent\AfterConsume $event
     */
    protected function handleKafkaMessageProcessed(object $event): void
    {
        if (! $this->feature->isEnabled('kafka')) {
            return;
        }
    }

    /**
     * @param KafkaEvent\FailToConsume $event
     */
    protected function handleKafkaMessageFailed(object $event): void
    {
        if (! $this->feature->isEnabled('kafka')) {
            return;
        }

        $this->captureException($event->getThrowable());
    }

    /**
     * Extract the command input arguments if possible.
     */
    private function extractConsoleCommandInput(?InputInterface $input): ?string
    {
        if ($input instanceof ArgvInput) {
            return (string) $input;
        }

        return null;
    }
}
