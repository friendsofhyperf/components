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
 *
 * @param array|int $times
 * @param Closure|int $sleepMilliseconds
 * @param callable|null $when
 * @return mixed
 *
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
 * @template T
 *
 * @param (callable(): T) $callback
 * @return T
 */
function once(callable $callback): mixed
{
    $trace = debug_backtrace(
        DEBUG_BACKTRACE_PROVIDE_OBJECT,
        2
    );

    $backtrace = new Once\Backtrace($trace);

    if ($backtrace->getFunctionName() === 'eval') {
        return call_user_func($callback);
    }

    $object = $backtrace->getObject();
    $hash = $backtrace->getHash();
    $cache = Once\Cache::getInstance();

    if (is_string($object)) {
        $object = $cache;
    }

    if (! $cache->isEnabled()) {
        return call_user_func($callback, $backtrace->getArguments());
    }

    if (! $cache->has($object, $hash)) {
        $result = call_user_func($callback, $backtrace->getArguments());
        $cache->set($object, $hash, $result);
    }

    return $cache->get($object, $hash);
}
