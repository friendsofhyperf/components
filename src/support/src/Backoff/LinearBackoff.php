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
 * Linear backoff strategy implementation.
 *
 * This class implements a linear backoff algorithm where the delay increases
 * linearly with each attempt. The delay is calculated as:
 * delay = initial + (attempt * step)
 *
 * The delay is capped at the maximum value to prevent excessive waiting times.
 */
class LinearBackoff implements BackoffInterface
{
    /**
     * The initial delay in milliseconds for the first attempt.
     */
    private int $initial;

    /**
     * The step size in milliseconds to increase the delay for each subsequent attempt.
     */
    private int $step;

    /**
     * The maximum delay in milliseconds that will never be exceeded.
     */
    private int $max;

    /**
     * The current attempt number, starting from 0.
     */
    private int $attempt = 0;

    /**
     * Create a new linear backoff instance.
     *
     * @param int $initial The initial delay in milliseconds (default: 100ms)
     * @param int $step The step size to increase delay per attempt (default: 50ms)
     * @param int $max The maximum delay cap in milliseconds (default: 2000ms)
     */
    public function __construct(int $initial = 100, int $step = 50, int $max = 2000)
    {
        $this->initial = $initial;
        $this->step = $step;
        $this->max = $max;
    }

    /**
     * Calculate and return the next delay value for the current attempt.
     *
     * This method calculates the delay using the linear formula:
     * delay = initial + (attempt * step)
     *
     * The calculated delay is capped at the maximum value to prevent
     * excessively long delays. After calculating the delay, the attempt
     * counter is incremented for the next call.
     *
     * @return int The delay in milliseconds for the current attempt
     */
    public function next(): int
    {
        // Calculate linear delay: initial + (attempt * step)
        $delay = $this->initial + $this->attempt * $this->step;

        // Increment attempt counter for next calculation
        ++$this->attempt;

        // Cap the delay at the maximum value
        if ($delay > $this->max) {
            $delay = $this->max;
        }

        return $delay;
    }

    /**
     * Reset the backoff state to its initial condition.
     *
     * This method resets the attempt counter back to 0, effectively
     * starting the backoff sequence over from the beginning.
     */
    public function reset(): void
    {
        $this->attempt = 0;
    }

    /**
     * Get the current attempt number.
     *
     * Returns the number of times the next() method has been called
     * since the last reset or instantiation.
     *
     * @return int The current attempt number (0-based)
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
