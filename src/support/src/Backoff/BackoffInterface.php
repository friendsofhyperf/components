<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Backoff;

/**
 * Backoff algorithm interface
 * Used to implement delay time calculation in retry mechanisms.
 */
interface BackoffInterface
{
    /**
     * Get the delay time for the next retry (milliseconds).
     *
     * @return int Delay time in milliseconds
     */
    public function next(): int;

    /**
     * Reset backoff state
     * Reset retry count and related state to initial values.
     */
    public function reset(): void;

    /**
     * Get the current retry count.
     *
     * @return int Current number of retries
     */
    public function getAttempt(): int;
}
