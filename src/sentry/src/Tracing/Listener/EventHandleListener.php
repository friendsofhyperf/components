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

use Closure;
use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Integration;
use FriendsOfHyperf\Sentry\Util\Carrier;
use FriendsOfHyperf\Sentry\Util\CoContainer;
use FriendsOfHyperf\Sentry\Util\SqlParser;
use Hyperf\Amqp\Event as AmqpEvent;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\AsyncQueue\Event as AsyncQueueEvent;
use Hyperf\Command\Event as CommandEvent;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Crontab\Event as CrontabEvent;
use Hyperf\Database\Events as DbEvent;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event as HttpEvent;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Kafka\Event as KafkaEvent;
use Hyperf\Redis\Event as RedisEvent;
use Hyperf\Redis\Pool\PoolFactory as RedisPoolFactory;
use Hyperf\Rpc\Context as RpcContext;
use Hyperf\RpcServer\Event as RpcEvent;
use Hyperf\Stringable\Str;
use longlang\phpkafka\Consumer\ConsumeMessage;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Container\ContainerInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Swow\Psr7\Message\ResponsePlusInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

use function FriendsOfHyperf\Sentry\startTransaction;
use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Coroutine\defer;
use function Sentry\continueTrace;

/**
 * @property int $exitCode
 * @property \Symfony\Component\Console\Input\InputInterface $input
 */
class EventHandleListener implements ListenerInterface
{
    protected string $source = 'route';

    protected array $ignoreCommands = [];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected Feature $feature
    ) {
        $this->ignoreCommands = (array) $this->config->get('sentry.ignore_commands', []);
    }

    public function listen(): array
    {
        return [
            // Database events
            DbEvent\QueryExecuted::class,
            DbEvent\TransactionBeginning::class,
            DbEvent\TransactionCommitted::class,
            DbEvent\TransactionRolledBack::class,

            // Request events
            HttpEvent\RequestReceived::class,
            HttpEvent\RequestHandled::class,
            RpcEvent\RequestReceived::class,
            RpcEvent\RequestHandled::class,

            // Command events
            CommandEvent\BeforeHandle::class,
            CommandEvent\AfterExecute::class,

            // Redis events
            RedisEvent\CommandExecuted::class,

            // Crontab events
            CrontabEvent\BeforeExecute::class,
            CrontabEvent\FailToExecute::class,
            CrontabEvent\AfterExecute::class,

            // AMQP events
            AmqpEvent\BeforeConsume::class,
            AmqpEvent\AfterConsume::class,
            AmqpEvent\FailToConsume::class,

            // Kafka events
            KafkaEvent\BeforeConsume::class,
            KafkaEvent\AfterConsume::class,
            KafkaEvent\FailToConsume::class,

            // AsyncQueue events
            AsyncQueueEvent\BeforeHandle::class,
            AsyncQueueEvent\AfterHandle::class,
            AsyncQueueEvent\RetryHandle::class,
            AsyncQueueEvent\FailedHandle::class,
        ];
    }

    public function process(object $event): void
    {
        match ($event::class) {
            // Database
            DbEvent\QueryExecuted::class => $this->handleDbQueryExecuted($event),
            DbEvent\TransactionBeginning::class => $this->handleDbTransactionBeginning($event),
            DbEvent\TransactionCommitted::class => $this->handleDbTransactionCommitted($event),
            DbEvent\TransactionRolledBack::class => $this->handleDbTransactionRolledBack($event),

            // Request
            HttpEvent\RequestReceived::class,
            RpcEvent\RequestReceived::class => $this->handleRequestReceived($event),
            HttpEvent\RequestHandled::class,
            RpcEvent\RequestHandled::class => $this->handleRequestHandled($event),

            // Command
            CommandEvent\BeforeHandle::class => $this->handleCommandStarting($event),
            CommandEvent\AfterExecute::class => $this->handleCommandFinished($event),

            // Redis
            RedisEvent\CommandExecuted::class => $this->handleRedisCommandExecuted($event),

            // Crontab
            CrontabEvent\BeforeExecute::class => $this->handleCrontabTaskStarting($event),
            CrontabEvent\FailToExecute::class,
            CrontabEvent\AfterExecute::class => $this->handleCrontabTaskFinished($event),

            // AMQP
            AmqpEvent\BeforeConsume::class => $this->handleAmqpMessageProcessing($event),
            AmqpEvent\AfterConsume::class,
            AmqpEvent\FailToConsume::class => $this->handleAmqpMessageProcessed($event),

            // Kafka
            KafkaEvent\BeforeConsume::class => $this->handleKafkaMessageProcessing($event),
            KafkaEvent\AfterConsume::class,
            KafkaEvent\FailToConsume::class => $this->handleKafkaMessageProcessed($event),

            // AsyncQueue
            AsyncQueueEvent\BeforeHandle::class => $this->handleAsyncQueueJobProcessing($event),
            AsyncQueueEvent\RetryHandle::class,
            AsyncQueueEvent\FailedHandle::class,
            AsyncQueueEvent\AfterHandle::class => $this->handleAsyncQueueJobProcessed($event),

            default => null,
        };
    }

    protected function handleDbQueryExecuted(DbEvent\QueryExecuted $event): void
    {
        if (! $this->feature->isTracingSpanEnabled('sql_queries')) {
            return;
        }

        $data = [
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

        $pool = $this->container->get(PoolFactory::class)->getPool($event->connectionName);
        $data += [
            'db.pool.name' => $event->connectionName,
            'db.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.pool.idle' => $pool->getConnectionsInChannel(),
            'db.pool.using' => $pool->getCurrentConnections(),
        ];

        if ($this->feature->isTracingExtraTagEnabled('db.sql.bindings', true)) {
            $data['db.sql.bindings'] = $event->bindings;
        }

        $startTimestamp = microtime(true) - $event->time / 1000;

        trace(
            fn () => null,
            SpanContext::make()
                ->setOp('db.sql.query')
                ->setDescription($event->sql)
                ->setOrigin('auto.db')
                ->setStartTimestamp($startTimestamp)
                ->setData($data)
                ->setEndTimestamp($startTimestamp + $event->time / 1000)
        );
    }

    protected function handleDbTransactionBeginning(DbEvent\TransactionBeginning $event): void
    {
        if (! $this->feature->isTracingSpanEnabled('sql_transactions')) {
            return;
        }

        trace(
            fn () => null,
            SpanContext::make()
                ->setOp('db.transaction')
                ->setDescription('BEGIN')
                ->setOrigin('auto.db')
                ->setStartTimestamp(microtime(true))
                ->setData([
                    'db.system' => $event->connection->getDriverName(),
                    'db.name' => $event->connection->getDatabaseName(),
                    'db.pool.name' => $event->connectionName,
                ])
        );
    }

    protected function handleDbTransactionCommitted(DbEvent\TransactionCommitted $event): void
    {
        if (! $this->feature->isTracingSpanEnabled('sql_transactions')) {
            return;
        }
        if (! $span = SentrySdk::getCurrentHub()->getSpan()) {
            return;
        }

        $span->setStatus(SpanStatus::ok())
            ->setDescription('COMMIT')
            ->finish();
    }

    protected function handleDbTransactionRolledBack(DbEvent\TransactionRolledBack $event): void
    {
        if (! $this->feature->isTracingSpanEnabled('sql_transactions')) {
            return;
        }
        if (! $span = SentrySdk::getCurrentHub()->getSpan()) {
            return;
        }

        $span->setStatus(SpanStatus::internalError())
            ->setDescription('ROLLBACK')
            ->finish();
    }

    protected function handleRequestReceived(HttpEvent\RequestReceived|RpcEvent\RequestReceived $event): void
    {
        if (! $this->feature->isTracingEnabled('request')) {
            return;
        }

        $request = $event->request;
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched->isFound() && ! $this->feature->isTracingEnabled('missing_routes')) {
            return;
        }

        $serverName = $dispatched->serverName ?? 'http';
        $path = $request->getUri()->getPath();
        $method = strtoupper($request->getMethod());
        [$route, $routeParams, $routeCallback] = $this->parseRoute($dispatched);
        [$name, $source] = match (strtolower($this->source)) {
            'custom' => [$routeCallback, TransactionSource::custom()],
            'url' => [$path, TransactionSource::url()],
            default => [$route, TransactionSource::route()],
        };
        $data = [
            'coroutine.id' => Coroutine::id(),
            'url.scheme' => $request->getUri()->getScheme(),
            'url.path' => $path,
            'http.request.method' => $method,
            'http.route' => $route,
            'http.route.params' => $routeParams,
        ];
        foreach ($request->getHeaders() as $key => $value) {
            $data['http.request.header.' . $key] = implode(', ', $value);
        }
        if ($this->container->has(RpcContext::class)) {
            $data['rpc.context'] = $this->container->get(RpcContext::class)->getData();
        }

        $carrier = Carrier::fromRequest($request);
        $transaction = startTransaction(
            continueTrace($carrier->getSentryTrace(), $carrier->getBaggage())
                ->setName($name)
                ->setOp(sprintf('%s.server', $serverName))
                ->setDescription(description: sprintf('%s %s', $method, $path))
                ->setOrigin('auto.request')
                ->setSource($source)
                ->setData($data)
        );

        if (! $transaction->getSampled()) {
            return;
        }

        $span = $transaction->startChild(
            SpanContext::make()
                ->setOp('request.received')
                ->setDescription('request.received')
                ->setData(['coroutine.id' => Coroutine::id()])
                ->setStatus(SpanStatus::ok())
                ->setStartTimestamp(microtime(true))
        );

        SentrySdk::getCurrentHub()->setSpan($span);

        defer(function () use ($transaction, $span) {
            // Make sure the span is finished after the request is handled
            $span->finish();

            // Make sure the transaction is finished after the request is handled
            SentrySdk::getCurrentHub()->setSpan($transaction);

            // Finish transaction
            $transaction->finish();

            // Flush events
            Integration::flushEvents();
        });
    }

    protected function handleRequestHandled(HttpEvent\RequestHandled|RpcEvent\RequestHandled $event): void
    {
        $span = SentrySdk::getCurrentHub()->getSpan();

        if (! $span) {
            return;
        }

        $traceId = (string) $span->getTraceId();

        if ($event instanceof RpcEvent\RequestHandled && $this->container->has(RpcContext::class)) {
            $this->container->get(RpcContext::class)->set('sentry-trace-id', $traceId);
        } elseif ($event->response instanceof ResponsePlusInterface) {
            $event->response->setHeader('sentry-trace-id', $traceId);
        }

        $span->setHttpStatus($event->response->getStatusCode());

        if (method_exists($event, 'getThrowable') && $event->getThrowable()) {
            $span->setStatus(SpanStatus::internalError());
        }
    }

    protected function handleCommandStarting(CommandEvent\BeforeHandle $event): void
    {
        if (
            ! $this->feature->isTracingEnabled('command')
            || Str::is($this->ignoreCommands, $event->getCommand()->getName())
        ) {
            return;
        }

        $command = $event->getCommand();

        if (! $parentSpan = SentrySdk::getCurrentHub()->getSpan()) {
            $parentSpan = startTransaction(
                TransactionContext::make()
                    ->setName($command->getName() ?: '<unnamed command>')
                    ->setOp('console.command')
                    ->setDescription($command->getDescription())
                    ->setOrigin('auto.console')
                    ->setData([
                        'command.arguments' => (fn () => $this->input->getArguments())->call($command),
                        'command.options' => (fn () => $this->input->getOptions())->call($command),
                    ])
                    ->setTags([
                        'command.name' => $command->getName(),
                    ])
                    ->setSource(TransactionSource::task())
            );

            CoContainer::set($command, $parentSpan);
        }

        if (! $parentSpan->getSampled()) {
            CoContainer::del($command);
            return;
        }

        $scope = SentrySdk::getCurrentHub()->pushScope();
        $span = $parentSpan->startChild(
            SpanContext::make()
                ->setOp('console.command.execute')
                ->setDescription($command->getName() ?: $command->getDescription())
                ->setData([
                    'command.arguments' => (fn () => $this->input->getArguments())->call($command),
                    'command.options' => (fn () => $this->input->getOptions())->call($command),
                ])
                ->setTags([
                    'command.name' => $command->getName(),
                ])
        );
        $scope->setSpan($span);
    }

    protected function handleCommandFinished(CommandEvent\AfterExecute $event): void
    {
        $span = SentrySdk::getCurrentHub()->getSpan();

        if (! $span instanceof Span) {
            return;
        }

        $command = $event->getCommand();
        $sampled = $span->getSampled();

        try {
            if (! $sampled) {
                return;
            }

            /** @var int $exitCode */
            $exitCode = (fn () => $this->exitCode ?? SymfonyCommand::SUCCESS)->call($command);

            $span->setTags([
                'command.exit_code' => (string) $exitCode,
            ])->setStatus(
                $event->getThrowable() || $exitCode !== SymfonyCommand::SUCCESS
                ? SpanStatus::internalError()
                : SpanStatus::ok()
            );
        } finally {
            if ($sampled) {
                $span->finish();
                Integration::flushEvents();
            }

            $parentSpan = CoContainer::pull($command);

            if ($parentSpan instanceof Span) {
                SentrySdk::getCurrentHub()->setSpan($parentSpan);
                $parentSpan->finish();
                SentrySdk::getCurrentHub()->popScope();
            }
        }
    }

    protected function handleRedisCommandExecuted(RedisEvent\CommandExecuted $event): void
    {
        if (! $this->feature->isTracingSpanEnabled('redis')) {
            return;
        }

        $pool = $this->container->get(RedisPoolFactory::class)->getPool($event->connectionName);
        $config = $this->config->get('redis.' . $event->connectionName, []);
        $redisStatement = $event->getFormatCommand();

        trace(
            function (Scope $scope) use ($event) {
                if (! $span = $scope->getSpan()) {
                    return;
                }

                if ($this->feature->isTracingExtraTagEnabled('redis.result')) {
                    $span->setData(['db.redis.result' => $event->result]);
                }

                if ($event->throwable) {
                    $span->setStatus(SpanStatus::internalError());
                }
            },
            SpanContext::make()
                ->setOp('db.redis')
                ->setDescription($redisStatement)
                ->setData([
                    'db.system' => 'redis',
                    'db.statement' => $redisStatement,
                    'db.redis.connection' => $event->connectionName,
                    'db.redis.database_index' => $config['db'] ?? 0,
                    'db.redis.parameters' => $event->parameters,
                    'db.redis.pool.name' => $event->connectionName,
                    'db.redis.pool.max' => $pool->getOption()->getMaxConnections(),
                    'db.redis.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
                    'db.redis.pool.idle' => $pool->getConnectionsInChannel(),
                    'db.redis.pool.using' => $pool->getCurrentConnections(),
                    'duration' => $event->time * 1000,
                ])
                ->setStartTimestamp(microtime(true) - $event->time / 1000)
        );
    }

    protected function handleCrontabTaskStarting(CrontabEvent\BeforeExecute $event): void
    {
        if (! $this->feature->isTracingEnabled('crontab')) {
            return;
        }

        $crontab = $event->crontab;

        $transaction = startTransaction(
            TransactionContext::make()
                ->setName($crontab->getName() ?: '<unnamed crontab>')
                ->setOp('crontab.run')
                ->setDescription($crontab->getMemo())
                ->setOrigin('auto.crontab')
                ->setSource(TransactionSource::task())
                ->setTags([
                    'crontab.rule' => $crontab->getRule(),
                    'crontab.type' => $crontab->getType(),
                ])
                ->setData([
                    'crontab.options.is_single' => $crontab->isSingleton(),
                    'crontab.options.is_on_one_server' => $crontab->isOnOneServer(),
                ])
        );

        defer(function () use ($transaction) {
            // Make sure the transaction is finished after the task is executed
            SentrySdk::getCurrentHub()->setSpan($transaction);

            // Finish transaction
            $transaction->finish();

            // Flush events
            Integration::flushEvents();
        });
    }

    protected function handleCrontabTaskFinished(CrontabEvent\FailToExecute|CrontabEvent\AfterExecute $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction?->getSampled()) {
            return;
        }

        if ($event instanceof CrontabEvent\FailToExecute) {
            $transaction->setStatus(SpanStatus::internalError());
        }
    }

    protected function handleAmqpMessageProcessing(AmqpEvent\BeforeConsume $event): void
    {
        if (! $this->feature->isTracingEnabled('amqp')) {
            return;
        }

        /** @var ConsumerMessage $message */
        $message = $event->getMessage();
        $carrier = null;

        if (method_exists($event, 'getAMQPMessage')) {
            /** @var AMQPMessage $amqpMessage */
            $amqpMessage = $event->getAMQPMessage();
            /** @var null|AMQPTable $applicationHeaders */
            $applicationHeaders = $amqpMessage->has('application_headers') ? $amqpMessage->get('application_headers') : null;
            if ($applicationHeaders && isset($applicationHeaders[Constants::TRACE_CARRIER])) {
                $carrier = Carrier::fromJson($applicationHeaders[Constants::TRACE_CARRIER]);
                Context::set(Constants::TRACE_CARRIER, $carrier);
            }
        }

        $transaction = startTransaction(
            continueTrace($carrier?->getSentryTrace() ?? '', $carrier?->getBaggage() ?? '')
                ->setName($message::class)
                ->setOp('queue.process')
                ->setDescription($message::class)
                ->setOrigin('auto.amqp')
                ->setData([
                    'messaging.system' => 'amqp',
                    'messaging.operation' => 'process',
                    'messaging.message.id' => $carrier?->get('message_id'),
                    'messaging.message.body.size' => $carrier?->get('body_size'),
                    'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
                    'messaging.message.retry.count' => 0,
                    'messaging.message.producer' => $carrier?->get('producer', 'unknown'),
                    'messaging.destination.name' => $carrier?->get('destination_name') ?: implode(', ', (array) $message->getRoutingKey()),
                    'messaging.amqp.message.type' => $message->getTypeString(),
                    'messaging.amqp.message.routing_key' => $message->getRoutingKey(),
                    'messaging.amqp.message.exchange' => $message->getExchange(),
                    'messaging.amqp.message.queue' => $message->getQueue(),
                    'messaging.amqp.message.pool_name' => $message->getPoolName(),
                ])
        );

        defer(function () use ($transaction) {
            // Make sure the transaction is finished after the message is processed
            SentrySdk::getCurrentHub()->setSpan($transaction);

            // Finish transaction
            $transaction->finish();

            // Flush events
            Integration::flushEvents();
        });
    }

    protected function handleAmqpMessageProcessed(AmqpEvent\AfterConsume|AmqpEvent\FailToConsume $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction?->getSampled()) {
            return;
        }

        $transaction->setData([
            'messaging.amqp.message.result' => $event instanceof AmqpEvent\AfterConsume ? $event->getResult()->value : 'fail',
        ]);

        if ($event instanceof AmqpEvent\FailToConsume) {
            $transaction->setStatus(SpanStatus::internalError());
        }
    }

    protected function handleKafkaMessageProcessing(KafkaEvent\BeforeConsume $event): void
    {
        if (! $this->feature->isTracingEnabled('kafka')) {
            return;
        }

        $consumer = $event->getConsumer();
        $message = $event->getData();
        $carrier = null;

        if ($message instanceof ConsumeMessage) {
            foreach ($message->getHeaders() as $header) {
                if ($header->getHeaderKey() === Constants::TRACE_CARRIER) {
                    $carrier = Carrier::fromJson($header->getValue());
                    Context::set(Constants::TRACE_CARRIER, $carrier);
                    break;
                }
            }
        }

        $transaction = startTransaction(
            continueTrace($carrier?->getSentryTrace() ?? '', $carrier?->getBaggage() ?? '')
                ->setName($consumer->getTopic() . ' process')
                ->setOp('queue.process')
                ->setDescription($consumer::class)
                ->setOrigin('auto.kafka')
                ->setData([
                    'messaging.system' => 'kafka',
                    'messaging.operation' => 'process',
                    'messaging.message.id' => $carrier?->get('message_id'),
                    'messaging.message.body.size' => $carrier?->get('body_size'),
                    'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
                    'messaging.message.retry.count' => 0,
                    'messaging.destination.name' => $carrier?->get('destination_name') ?: (is_array($consumer->getTopic()) ? implode(',', $consumer->getTopic()) : $consumer->getTopic()),
                    'messaging.kafka.consumer.group' => $consumer->getGroupId(),
                    'messaging.kafka.consumer.pool' => $consumer->getPool(),
                ])
        );

        defer(function () use ($transaction) {
            // Make sure the transaction is finished after the message is processed
            SentrySdk::getCurrentHub()->setSpan($transaction);

            // Finish transaction
            $transaction->finish();

            // Flush events
            Integration::flushEvents();
        });
    }

    protected function handleKafkaMessageProcessed(KafkaEvent\AfterConsume|KafkaEvent\FailToConsume $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction?->getSampled()) {
            return;
        }

        if ($event instanceof KafkaEvent\FailToConsume) {
            $transaction->setStatus(SpanStatus::internalError());
        }
    }

    protected function handleAsyncQueueJobProcessing(AsyncQueueEvent\BeforeHandle $event): void
    {
        if (! $this->feature->isTracingEnabled('async_queue')) {
            return;
        }

        /** @var null|Carrier $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER, null, Coroutine::parentId());
        $job = $event->getMessage()->job();

        $transaction = startTransaction(
            continueTrace($carrier?->getSentryTrace() ?? '', $carrier?->getBaggage() ?? '')
                ->setName($job::class)
                ->setOp('queue.process')
                ->setDescription('async_queue: ' . $job::class)
                ->setOrigin('auto.async_queue')->setData([
                    'messaging.system' => 'async_queue',
                    'messaging.operation' => 'process',
                    'messaging.message.id' => $carrier?->get('message_id'),
                    'messaging.message.body.size' => $carrier?->get('body_size'),
                    'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
                    'messaging.message.retry.count' => $event->getMessage()->getAttempts(),
                    'messaging.destination.name' => $carrier?->get('destination_name') ?: 'unknown queue',
                ])
        );

        defer(function () use ($transaction) {
            // Make sure the transaction is finished after the job is processed
            SentrySdk::getCurrentHub()->setSpan($transaction);

            // Finish transaction
            $transaction->finish();

            // Flush events
            Integration::flushEvents();
        });
    }

    protected function handleAsyncQueueJobProcessed(AsyncQueueEvent\AfterHandle|AsyncQueueEvent\RetryHandle|AsyncQueueEvent\FailedHandle $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction?->getSampled()) {
            return;
        }

        if ($event instanceof AsyncQueueEvent\FailedHandle) {
            $transaction->setStatus(SpanStatus::internalError());
        }
    }

    /**
     * @return array{0:string,1:array,2:string}
     */
    private function parseRoute(Dispatched $dispatched): array
    {
        $route = '<missing route>';
        $params = [];
        $callback = '';

        if ($dispatched instanceof Dispatched && $dispatched->isFound()) {
            $route = $dispatched->handler->route;
            $params = $dispatched->params;
            $callback = match (true) {
                $dispatched->handler->callback instanceof Closure => 'closure',
                is_array($dispatched->handler->callback) => implode('@', $dispatched->handler->callback),
                is_string($dispatched->handler->callback) => $dispatched->handler->callback,
                default => $callback,
            };
        }

        return [$route, $params, $callback];
    }
}
