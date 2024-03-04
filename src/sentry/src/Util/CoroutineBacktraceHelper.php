<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Util;

class CoroutineBacktraceHelper
{
    protected static array $skipFunctions = [
        'Hyperf\Coordinator\Timer->after',
        'Hyperf\Coordinator\Timer->tick',
        'Hyperf\Coroutine\Concurrent->create',
        'Hyperf\Coroutine\Parallel->wait',
        'Hyperf\Coroutine\Waiter->wait',
        'Hyperf\Coroutine\co', 'co',
        'Hyperf\Coroutine\go', 'go',
        'Hyperf\Coroutine\parallel', 'parallel',
    ];

    protected static array $breakFunctions = [
        'Multiplex\Socket\Client->loop', 'Multiplex\Socket\Client->heartbeat',
        'FriendsOfHyperf\Sentry\HttpClient\HttpClient->loop',
        'Hyperf\Kafka\Producer->loop',
        'Hyperf\Metric\Listener\OnMetricFactoryReady->process',
        'Hyperf\Metric\Listener\QueueWatcher->process',
        'Hyperf\Metric\Listener\OnBeforeHandle->process',
        'Hyperf\Metric\Listener\OnBeforeHandle->spawnHandle',
        'Hyperf\Metric\Adapter\Prometheus\MetricFactory->scrapeHandle',
        'Hyperf\Amqp\AMQPConnection->loop', 'Hyperf\Amqp\AMQPConnection->heartbeat',
    ];

    public static function foundCallingOnFunction(): ?string
    {
        $found = false;

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $backtrace) {
            $function = static::compactFunction(
                $backtrace['function'],
                $backtrace['class'] ?? null,
                $backtrace['type'] ?? null
            );
            if (in_array($function, static::$breakFunctions, true)) {
                break;
            }
            if (in_array($function, static::$skipFunctions, true)) {
                continue;
            }
            if ($found === true) {
                return $function;
            }
            if ($function === 'Hyperf\Coroutine\Coroutine::create') {
                $found = true;
            }
        }

        return null;
    }

    protected static function compactFunction(string $function, ?string $class, ?string $type): string
    {
        return sprintf('%s%s%s', $class, $type, $function);
    }
}
