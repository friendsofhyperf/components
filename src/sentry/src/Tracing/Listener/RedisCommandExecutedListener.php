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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\Pool\PoolFactory;
use Psr\Container\ContainerInterface;
use Sentry\Tracing\SpanStatus;

class RedisCommandExecutedListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected Switcher $switcher
    ) {
        $this->setRedisEventEnable();
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

        $arguments = $event->parameters;

        $poolName = $event->connectionName;
        $pool = $this->container->get(PoolFactory::class)->getPool($poolName);
        $config = $this->config->get('redis.' . $poolName, []);

        $data = [
            'coroutine.id' => Coroutine::id(),
            'db.system' => 'redis',
            'db.redis.connection' => $poolName,
            'db.redis.database_index' => $config['db'] ?? 0,
            'db.redis.parameters' => $arguments['arguments'],
            // 'db.statement' => strtoupper($arguments['name']) . implode(' ', $arguments['arguments']),
            'db.redis.pool.name' => $poolName,
            'db.redis.pool.max' => $pool->getOption()->getMaxConnections(),
            'db.redis.pool.max_idle_time' => $pool->getOption()->getMaxIdleTime(),
            'db.redis.pool.idle' => $pool->getConnectionsInChannel(),
            'db.redis.pool.using' => $pool->getCurrentConnections(),
        ];

        // rule: operation db.table
        // $op = sprintf('%s %s', $arguments['name'], $arguments['arguments']['key'] ?? '');
        // $description = sprintf('%s::%s()', $proceedingJoinPoint->className, $arguments['name']);
        $key = $arguments['arguments'][0] ?? '';
        $op = 'db.redis';
        $description = sprintf(
            '%s %s',
            strtoupper($arguments['name'] ?? ''),
            is_array($key) ? implode(',', $key) : $key
        );
        $span = $this->startSpan($op, $description);

        if (! $span) {
            return;
        }

        if ($this->switcher->isTracingExtraTagEnable('redis.result')) {
            $data['redis.result'] = $event->result;
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

            throw $exception;
        }

        $span->setData($data);
        $span->finish();
    }

    private function setRedisEventEnable()
    {
        foreach ((array) $this->config->get('redis', []) as $connection => $_) {
            $this->config->set('redis.' . $connection . '.event.enable', true);
        }
    }
}
