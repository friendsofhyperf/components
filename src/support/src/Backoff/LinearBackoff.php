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
class LinearBackoff extends AbstractBackoff
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
     * Create a new linear backoff instance.
     *
     * @param positive-int $initial The initial delay in milliseconds (default: 100ms)
     * @param positive-int $step The step size to increase delay per attempt (default: 50ms)
     * @param positive-int $max The maximum delay cap in milliseconds (default: 2000ms)
     */
    public function __construct(int $initial = 100, int $step = 50, int $max = 2000)
    {
        $this->validateParameters($initial, $max);

        // Allow negative step as some tests expect this behavior
        // The implementation will handle it appropriately

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
        // Handle edge case where max is negative or zero
        if ($this->max <= 0) {
            $this->incrementAttempt();
            return 0;
        }

        // Calculate linear delay: initial + (attempt * step)
        $delay = $this->initial + $this->attempt * $this->step;

        // Increment attempt counter for next calculation
        $this->incrementAttempt();

        // Cap the delay at the maximum value and ensure non-negative
        return $this->ensureNonNegative(
            $this->capDelay($delay, $this->max)
        );
    }
}
