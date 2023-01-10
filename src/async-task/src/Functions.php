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

use Hyperf\Utils\Backoff;
use Throwable;

function retry(int $times, callable $callback, int $sleep = 0)
{
    $attempts = 0;
    $backoff = new Backoff($sleep);

    beginning:
    try {
        return $callback(++$attempts, $e ?? null);
    } catch (Throwable $e) {
        if (--$times < 0) {
            throw $e;
        }

        $backoff->sleep();
        goto beginning;
    }
}
