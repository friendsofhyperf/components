<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AsyncTask;

use Throwable;

/**
 * Retry.
 * @return mixed
 * @throws Throwable
 * @deprecated since 3.1, use `Hyperf\Support\retry` instead.
 */
function retry(int $times, callable $callback, int $sleep = 0, callable $when = null)
{
    $attempts = 0;

    beginning:
    $attempts++;
    --$times;

    try {
        return $callback($attempts);
    } catch (Throwable $e) {
        if ($times < 1 || ($when && ! $when($e))) {
            throw $e;
        }

        if ($sleep) {
            usleep($sleep * 1000);
        }

        goto beginning;
    }
}
