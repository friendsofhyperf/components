<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncQueueClosureJob;

use Closure;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Context\ApplicationContext;

/**
 * Dispatch a closure as an async queue job.
 *
 * @param Closure $closure The closure to execute
 * @param-closure-this ClosureJob $closure
 * @param string $queue The queue name (default: 'default')
 * @param int $delay The delay in seconds before execution (default: 0)
 * @param int $maxAttempts Maximum number of attempts (default: 0)
 */
function dispatch(Closure $closure, string $queue = 'default', int $delay = 0, int $maxAttempts = 0): bool
{
    $job = new ClosureJob($closure, $maxAttempts);

    return ApplicationContext::getContainer()
        ->get(DriverFactory::class)
        ->get($queue)
        ->push($job, $delay);
}
