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
class FixedBackoff implements BackoffInterface
{
    /**
     * The fixed delay in milliseconds between retry attempts.
     * This value remains constant for all retry attempts.
     */
    private int $delay;

    /**
     * The current attempt counter.
     * Tracks how many retry attempts have been made.
     */
    private int $attempt = 0;

    /**
     * Constructor to initialize the fixed backoff strategy.
     *
     * @param int $delay The delay in milliseconds to wait between retry attempts (default: 500ms)
     */
    public function __construct(int $delay = 500)
    {
        $this->delay = $delay;
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
        ++$this->attempt;
        return $this->delay;
    }

    /**
     * Reset the backoff state.
     *
     * This resets the attempt counter back to 0, effectively
     * restarting the backoff sequence. This is typically called
     * when a retry operation succeeds or when starting a new
     * retry sequence.
     */
    public function reset(): void
    {
        $this->attempt = 0;
    }

    /**
     * Get the current attempt number.
     *
     * Returns the number of retry attempts that have been made
     * since the last reset. This is useful for logging or
     * implementing maximum retry limits.
     *
     * @return int The current attempt number (0-based)
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
