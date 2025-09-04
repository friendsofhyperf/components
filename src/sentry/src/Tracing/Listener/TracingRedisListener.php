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
        $redisStatement = (string) new RedisCommand($event->command, $event->parameters);

        $data = [
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
        ];

        // rule: operation db.table
        $op = 'db.redis';
        $span = $this->startSpan(
            op: $op,
            description: $redisStatement,
            origin: 'auto.cache.redis',
        );

        if (! $span) {
            return;
        }

        if ($this->switcher->isTracingExtraTagEnable('redis.result')) {
            $data['db.redis.result'] = $event->result;
        }

        if ($exception = $event->throwable) {
            $span->setStatus(SpanStatus::internalError())
                ->setTags([
                    'error' => true,
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => $exception->getCode(),
                ]);
            if ($this->switcher->isTracingExtraTagEnable('exception.stack_trace')) {
                $data['exception.stack_trace'] = (string) $exception;
            }
        }

        $span->setData($data)
            ->finish();
    }
}
