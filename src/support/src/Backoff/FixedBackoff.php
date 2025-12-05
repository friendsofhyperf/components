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
 * Fixed backoff implementation for retry mechanisms.
 *
 * This class provides a simple backoff strategy where the delay between
 * retry attempts remains constant (fixed). It's useful when you want
 * predictable retry intervals regardless of how many attempts have been made.
 */
class FixedBackoff extends AbstractBackoff
{
    /**
     * The fixed delay in milliseconds between retry attempts.
     * This value remains constant for all retry attempts.
     */
    private int $delay;

    /**
     * Constructor to initialize the fixed backoff strategy.
     *
     * @param positive-int $delay The delay in milliseconds to wait between retry attempts (default: 500ms)
     */
    public function __construct(int $delay = 500)
    {
        $this->delay = $this->ensureNonNegative($delay);
    }

    /**
     * Calculate the delay for the next retry attempt.
     *
     * For fixed backoff, this always returns the same delay value
     * regardless of the attempt number. The attempt counter is
     * incremented each time this method is called.
     *
     * @return int The delay in milliseconds before the next retry attempt
     */
    public function next(): int
    {
        $this->incrementAttempt();
        return $this->delay;
    }
}
