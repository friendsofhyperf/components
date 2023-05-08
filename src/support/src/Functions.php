<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
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
 * @param null|callable $when
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
            Sleep::usleep(value($sleepMilliseconds, $attempts, $e) * 1000);
        }

        goto beginning;
    }
}
