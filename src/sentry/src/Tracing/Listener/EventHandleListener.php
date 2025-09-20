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
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\SpanStarter;
use FriendsOfHyperf\Sentry\Util\Carrier;
use FriendsOfHyperf\Sentry\Util\SqlParser;
use FriendsOfHyperf\Support\RedisCommand;
use Hyperf\Amqp\Event as AmqpEvent;
use Hyperf\Amqp\Message\ConsumerMessage;
use Hyperf\AsyncQueue\Event as AsyncQueueEvent;
use Hyperf\Command\Event as CommandEvent;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Crontab\Event as CrontabEvent;
use Hyperf\Database\Events\QueryExecuted;
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
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TransactionSource;
use Swow\Psr7\Message\ResponsePlusInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

use function Hyperf\Coroutine\defer;

/**
 * @property int $exitCode
 * @property \Symfony\Component\Console\Input\InputInterface $input
 */
class EventHandleListener implements ListenerInterface
{
    use SpanStarter;

    protected string $source = 'route';

    protected array $ignoreCommands = [];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected Switcher $switcher
    ) {
        $this->ignoreCommands = (array) $this->config->get('sentry.ignore_commands', []);
    }

    public function listen(): array
    {
        return [
            // Database events
            QueryExecuted::class,

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
            QueryExecuted::class => $this->handleDbQueryExecuted($event),

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

    private function handleDbQueryExecuted(QueryExecuted $event): void
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
            'db.sql.bindings' => $event->bindings,
        ];

        $startTimestamp = microtime(true) - $event->time / 1000;

        $op = 'db.sql.query';
        $description = $event->sql;

        $this->startSpan(
            op: $op,
            description: $description,
            origin: 'auto.db'
        )?->setData($data)
            ->setStartTimestamp($startTimestamp)
            ->finish($startTimestamp + $event->time / 1000);
    }

    private function handleRequestReceived(HttpEvent\RequestReceived|RpcEvent\RequestReceived $event): void
    {
        if (! $this->switcher->isTracingEnable('request')) {
            return;
        }

        $request = $event->request;
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched->isFound() && ! $this->switcher->isTracingEnable('missing_routes')) {
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

        $transaction = $this->startRequestTransaction(
            request: $request,
            name: $name,
            op: sprintf('%s.server', $serverName),
            description: sprintf('%s %s', $method, $path),
            origin: 'auto.request',
            source: $source,
        );

        if (! $transaction->getSampled()) {
            return;
        }

        $data = [
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

        $transaction->setData($data);

        $span = $this->startSpan(
            op: 'request.received',
            description: 'request.received',
            origin: 'auto.request.received',
            asParent: true
        );

        defer(function () use ($transaction, $span) {
            $span?->finish();

            SentrySdk::getCurrentHub()->setSpan($transaction);

            $transaction->finish();
        });
    }

    private function handleRequestHandled(HttpEvent\RequestHandled|RpcEvent\RequestHandled $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (
            ! $transaction
            || ! $transaction->getSampled()
            || ! $traceId = (string) $transaction->getTraceId()
        ) {
            return;
        }

        if ($event instanceof RpcEvent\RequestHandled) {
            if ($this->container->has(RpcContext::class)) {
                $this->container->get(RpcContext::class)->set('sentry-trace-id', $traceId);
            }
        } elseif ($event->response instanceof ResponsePlusInterface) {
            $event->response->setHeader('sentry-trace-id', $traceId);
        }

        $transaction->setHttpStatus($event->response->getStatusCode());

        if ($exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.code' => (string) $exception->getCode(),
                    'exception.message' => $exception->getMessage(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $transaction->setData([
                    'exception.stack_trace' => (string) $exception,
                ]);
            }
        }
    }

    private function handleCommandStarting(CommandEvent\BeforeHandle $event): void
    {
        if (
            ! $this->switcher->isTracingEnable('command')
            || Str::is($this->ignoreCommands, $event->getCommand()->getName())
        ) {
            return;
        }

        $command = $event->getCommand();

        $this->continueTrace(
            name: $command->getName() ?: '<unnamed command>',
            op: 'console.command',
            description: $command->getDescription(),
            origin: 'auto.command',
            source: TransactionSource::custom()
        );
    }

    private function handleCommandFinished(CommandEvent\AfterExecute $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        $command = $event->getCommand();
        $exitCode = (fn () => $this->exitCode ?? SymfonyCommand::SUCCESS)->call($command);

        $transaction->setData([
            'command.arguments' => (fn () => $this->input->getArguments())->call($command),
            'command.options' => (fn () => $this->input->getOptions())->call($command),
        ])->setTags([
            'command.exit_code' => (string) $exitCode,
        ]);

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => (string) $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $transaction->setData([
                    'exception.stack_trace' => (string) $exception,
                ]);
            }
        }

        $transaction->setStatus(
            $exitCode == SymfonyCommand::SUCCESS ? SpanStatus::ok() : SpanStatus::internalError()
        );

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }

    private function handleRedisCommandExecuted(RedisEvent\CommandExecuted $event): void
    {
        if (! $this->switcher->isTracingSpanEnable('redis')) {
            return;
        }

        $pool = $this->container->get(RedisPoolFactory::class)->getPool($event->connectionName);
        $config = $this->config->get('redis.' . $event->connectionName, []);
        $redisStatement = (string) new RedisCommand($event->command, $event->parameters);

        $span = $this->startSpan(
            op: 'db.redis',
            description: $redisStatement,
            origin: 'auto.cache.redis',
        );

        if (! $span) {
            return;
        }

        $span->setData([
            'coroutine.id' => Coroutine::id(),
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
        ]);

        if ($this->switcher->isTracingExtraTagEnable('redis.result')) {
            $span->setData(['db.redis.result' => $event->result]);
        }

        if ($exception = $event->throwable) {
            $span->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => (string) $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $span->setData(['exception.stack_trace' => (string) $exception]);
            }
        }

        $span->finish();
    }

    private function handleCrontabTaskStarting(CrontabEvent\BeforeExecute $event): void
    {
        if (! $this->switcher->isTracingEnable('crontab')) {
            return;
        }

        $crontab = $event->crontab;

        $this->continueTrace(
            name: $crontab->getName() ?: '<unnamed crontab>',
            op: 'crontab.run',
            description: $crontab->getMemo(),
            origin: 'auto.crontab',
            source: TransactionSource::task()
        );
    }

    private function handleCrontabTaskFinished(CrontabEvent\FailToExecute|CrontabEvent\AfterExecute $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        $crontab = $event->crontab;
        $transaction->setTags([
            'crontab.rule' => $crontab->getRule(),
            'crontab.type' => $crontab->getType(),
            'crontab.options.is_single' => $crontab->isSingleton(),
            'crontab.options.is_on_one_server' => $crontab->isOnOneServer(),
        ]);

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => (string) $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $transaction->setData(['exception.stack_trace' => (string) $exception]);
            }
        }

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }

    private function handleAmqpMessageProcessing(AmqpEvent\BeforeConsume $event): void
    {
        if (! $this->switcher->isTracingEnable('amqp')) {
            return;
        }

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

        $this->continueTrace(
            sentryTrace: $carrier?->getSentryTrace() ?? '',
            baggage: $carrier?->getBaggage() ?? '',
            name: $message::class,
            op: 'queue.process',
            description: $message::class,
            origin: 'auto.amqp',
            source: TransactionSource::custom()
        );
    }

    private function handleAmqpMessageProcessed(AmqpEvent\AfterConsume|AmqpEvent\FailToConsume $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        /** @var null|Carrier $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER);

        /** @var ConsumerMessage $message */
        $message = $event->getMessage();
        $transaction->setData([
            'messaging.system' => 'amqp',
            'messaging.operation' => 'process',
            'messaging.message.id' => $carrier?->get('message_id'),
            'messaging.message.body.size' => $carrier?->get('body_size'),
            'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
            'messaging.message.retry.count' => 0,
            'messaging.destination.name' => $carrier?->get('destination_name') ?: implode(', ', (array) $message->getRoutingKey()),
            'messaging.amqp.message.type' => $message->getTypeString(),
            'messaging.amqp.message.routing_key' => $message->getRoutingKey(),
            'messaging.amqp.message.exchange' => $message->getExchange(),
            'messaging.amqp.message.queue' => $message->getQueue(),
            'messaging.amqp.message.pool_name' => $message->getPoolName(),
            'messaging.amqp.message.result' => $event instanceof AmqpEvent\AfterConsume ? $event->getResult()->value : 'fail',
        ]);

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => (string) $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $transaction->setData(['exception.stack_trace' => (string) $exception]);
            }
        }

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }

    private function handleKafkaMessageProcessing(KafkaEvent\BeforeConsume $event): void
    {
        if (! $this->switcher->isTracingEnable('kafka')) {
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

        $this->continueTrace(
            sentryTrace: $carrier?->getSentryTrace() ?? '',
            baggage: $carrier?->getBaggage() ?? '',
            name: $consumer->getTopic() . ' process',
            op: 'queue.process',
            description: $consumer::class,
            origin: 'auto.kafka',
            source: TransactionSource::custom()
        );
    }

    private function handleKafkaMessageProcessed(KafkaEvent\AfterConsume|KafkaEvent\FailToConsume $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        /** @var null|Carrier $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER);
        $consumer = $event->getConsumer();
        $transaction->setData([
            'messaging.system' => 'kafka',
            'messaging.operation' => 'process',
            'messaging.message.id' => $carrier?->get('message_id'),
            'messaging.message.body.size' => $carrier?->get('body_size'),
            'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
            'messaging.message.retry.count' => 0,
            'messaging.destination.name' => $carrier?->get('destination_name') ?: (is_array($consumer->getTopic()) ? implode(',', $consumer->getTopic()) : $consumer->getTopic()),
            'messaging.kafka.consumer.group' => $consumer->getGroupId(),
            'messaging.kafka.consumer.pool' => $consumer->getPool(),
        ]);

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => (string) $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $transaction->setData(['exception.stack_trace' => (string) $exception]);
            }
        }

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }

    private function handleAsyncQueueJobProcessing(AsyncQueueEvent\BeforeHandle $event): void
    {
        if (! $this->switcher->isTracingEnable('async_queue')) {
            return;
        }

        /** @var null|Carrier $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER, null, Coroutine::parentId());
        $job = $event->getMessage()->job();

        $this->continueTrace(
            sentryTrace: $carrier?->getSentryTrace() ?? '',
            baggage: $carrier?->getBaggage() ?? '',
            name: $job::class,
            op: 'queue.process',
            description: 'async_queue: ' . $job::class,
            origin: 'auto.async_queue',
            source: TransactionSource::custom()
        );
    }

    private function handleAsyncQueueJobProcessed(AsyncQueueEvent\AfterHandle|AsyncQueueEvent\RetryHandle|AsyncQueueEvent\FailedHandle $event): void
    {
        $transaction = SentrySdk::getCurrentHub()->getTransaction();

        if (! $transaction || ! $transaction->getSampled()) {
            return;
        }

        /** @var null|Carrier $carrier */
        $carrier = Context::get(Constants::TRACE_CARRIER, null, Coroutine::parentId());
        $transaction->setData([
            'messaging.system' => 'async_queue',
            'messaging.operation' => 'process',
            'messaging.message.id' => $carrier?->get('message_id'),
            'messaging.message.body.size' => $carrier?->get('body_size'),
            'messaging.message.receive.latency' => $carrier?->has('publish_time') ? (microtime(true) - $carrier->get('publish_time')) : null,
            'messaging.message.retry.count' => $event->getMessage()->getAttempts(),
            'messaging.destination.name' => $carrier?->get('destination_name') ?: 'unknown queue',
        ]);

        if (method_exists($event, 'getThrowable') && $exception = $event->getThrowable()) {
            $transaction->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => 'true',
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => (string) $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $transaction->setData(['exception.stack_trace' => (string) $exception]);
            }
        }

        SentrySdk::getCurrentHub()->setSpan($transaction);

        $transaction->finish(microtime(true));
    }

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
