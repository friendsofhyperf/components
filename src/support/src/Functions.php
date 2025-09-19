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

use function Hyperf\Support\value;

/**
 * Retry an operation a given number of times.
 * @template TReturn
 *
 * @param array|int $times
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

    if (is_array($times)) {
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
