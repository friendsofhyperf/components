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
use FriendsOfHyperf\Support\RedisCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\Pool\PoolFactory;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\SpanStatus;

class TracingRedisListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        private ContainerInterface $container,
        private ConfigInterface $config,
        private Switcher $switcher
    ) {
    }

    public function listen(): array
    {
        return [
            CommandExecuted::class,
        ];
    }

    /**
     * @param object|CommandExecuted $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isTracingSpanEnable('redis')) {
            return;
        }

        $pool = $this->container->get(PoolFactory::class)->getPool($event->connectionName);
        $config = $this->config->get('redis.' . $event->connectionName, []);

        $data = [
            'coroutine.id' => Coroutine::id(),
            'duration' => $event->time * 1000,
            'db.system' => 'redis',
            'db.redis.connection' => $event->connectionName,
            'db.redis.database_index' => $config['db'] ?? 0,
            'db.redis.parameters' => $event->parameters,
            'db.statement' => (new RedisCommand($event->command, $event->parameters))->__toString(),
            'db.redis.pool.name' => $event->connectionName,
            'db.redis.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.redis.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.redis.pool.idle' => $pool->getConnectionsInChannel(),
            'db.redis.pool.using' => $pool->getCurrentConnections(),
        ];

        // rule: operation db.table
        $op = 'db.redis';
        $description = sprintf(
            '%s %s',
            strtoupper($event->command),
            implode(' ', [$event->parameters[0] ?? '', $event->parameters[1] ?? ''])
        );
        $span = $this->startSpan($op, $description);

        if (! $span) {
            return;
        }

        if ($this->switcher->isTracingExtraTagEnable('redis.result')) {
            $data['db.redis.result'] = $event->result;
        }

        if ($exception = $event->throwable) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }
        }

        $span->setData($data);
        $span->finish();
    }
}
