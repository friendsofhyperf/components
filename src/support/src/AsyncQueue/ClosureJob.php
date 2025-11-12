<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\AsyncQueue;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;

/**
 * @deprecated since v3.2, will be removed in v3.2, use `FriendsOfHyperf\AsyncQueueClosureJob\ClosureJob` instead.
 */
class ClosureJob extends \FriendsOfHyperf\AsyncQueueClosureJob\CallQueuedClosure
{
    public function __construct(Closure $closure, int $maxAttempts = 0)
    {
        parent::__construct(new SerializableClosure($closure));

        if ($maxAttempts > 0) {
            $this->setMaxAttempts($maxAttempts);
        }
    }
}
