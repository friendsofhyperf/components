<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support;

use Closure;
use Exception;
use FriendsOfHyperf\Support\Bus\PendingAmqpProducerMessageDispatch;
use FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch;
use FriendsOfHyperf\Support\Bus\PendingKafkaProducerMessageDispatch;
use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\AsyncQueue\JobInterface;
use InvalidArgumentException;
use longlang\phpkafka\Producer\ProduceMessage;

use function Hyperf\Support\value;

/**
 * Do not assign a value to the return value of this function unless you are very clear about the consequences of doing so.
 * @param Closure|JobInterface|ProduceMessage|ProducerMessageInterface|mixed $job
 * @param-closure-this ($job is Closure ? CallQueuedClosure : mixed) $job
 * @return ($job is Closure ? PendingAsyncQueueDispatch : ( $job is JobInterface ? PendingAsyncQueueDispatch : ( $job is ProducerMessageInterface ? PendingAmqpProducerMessageDispatch : ( $job is ProduceMessage ? PendingKafkaProducerMessageDispatch : never ))))
 * @throws InvalidArgumentException
 */
function dispatch($job)
{
    if ($job instanceof Closure) {
        $job = CallQueuedClosure::create($job);
    }

    return match (true) {
        interface_exists(ProducerMessageInterface::class) && $job instanceof ProducerMessageInterface => new PendingAmqpProducerMessageDispatch($job),
        class_exists(ProduceMessage::class) && $job instanceof ProduceMessage => new PendingKafkaProducerMessageDispatch($job),
        interface_exists(JobInterface::class) && $job instanceof JobInterface => new PendingAsyncQueueDispatch($job),
        default => throw new InvalidArgumentException('Unsupported job type.')
    };
}

/**
 * Retry an operation a given number of times.
 * @template TReturn
 *
 * @param array<positive-int>|positive-int $times
 * @param callable(int):TReturn $callback
 * @param Closure|int $sleepMilliseconds
 * @param null|callable $when
 * @return TReturn|void
 * @throws Exception
 */
function retry($times, callable $callback, $sleepMilliseconds = 0, $when = null)
{
    $attempts = 0;
    $backoff = [];

    if (is_array($times)) { // array of backoff timings
        $backoff = $times;
        $times = count($times) + 1;
    }

    beginning:
    $attempts++;
    --$times;

    try {
        return $callback($attempts);
    } catch (Exception $e) {
        if ($times < 1 || ($when && ! $when($e))) {
            throw $e;
        }

        // If we have backoff timings defined and the current attempt has a corresponding
        // backoff time, we will use that. Otherwise, we will use the default sleep
        // time specified by the developer to pause execution before retrying.
        $sleepMilliseconds = $backoff[$attempts - 1] ?? $sleepMilliseconds;

        if ($sleepMilliseconds) {
            usleep(value($sleepMilliseconds, $attempts, $e) * 1000);
        }

        goto beginning;
    }
}

/**
 * Ensures a callable is only called once, and returns the result on subsequent calls.
 *
 * @template  TReturnType
 *
 * @param callable(): TReturnType $callback
 * @return TReturnType
 */
function once(callable $callback)
{
    $onceable = Onceable::tryFromTrace(
        debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2),
        $callback,
    );

    return $onceable ? Once::instance()->value($onceable) : call_user_func($callback);
}
