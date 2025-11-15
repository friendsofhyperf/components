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
use FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch;
use FriendsOfHyperf\Support\CallQueuedClosure;

use function FriendsOfHyperf\Support\dispatch as base_dispatch;

/**
 * Dispatch a closure as an async queue job.
 *
 * @param Closure $closure The closure to execute
 * @param-closure-this CallQueuedClosure $closure
 */
function dispatch(Closure $closure): PendingAsyncQueueDispatch
{
    return base_dispatch($closure);
}
