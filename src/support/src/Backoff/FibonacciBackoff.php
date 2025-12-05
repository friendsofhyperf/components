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
 * Implements a Fibonacci backoff strategy for retrying operations.
 *
 * The Fibonacci backoff increases the delay between retries according to the Fibonacci sequence:
 * 1, 1, 2, 3, 5, 8, 13, ... (in milliseconds by default). Each retry waits for the next Fibonacci number of milliseconds,
 * up to a configurable maximum delay.
 *
 * Use cases:
 * - Useful for retrying operations where a moderate, non-aggressive increase in delay is desired.
 * - Suitable for distributed systems, network requests, or resource contention scenarios where exponential backoff may be too aggressive,
 *   and linear backoff too slow.
 *
 * Compared to exponential backoff, Fibonacci backoff grows more slowly, reducing the risk of long wait times while still avoiding
 * overwhelming the system. It is preferable when you want a compromise between linear and exponential strategies.
 *
 * @see LinearBackoff
 * @see ExponentialBackoff
 * @see FixedBackoff
 */
class FibonacciBackoff extends AbstractBackoff
{
    /**
     * @var int Maximum allowed delay (milliseconds)
     */
    private int $max;

    /**
     * @var int Cache for previous Fibonacci number
     */
    private int $prev = 0;

    /**
     * @var int Cache for current Fibonacci number
     */
    private int $curr = 1;

    /**
     * Constructor.
     *
     * @param positive-int $max Maximum cap delay in milliseconds
     */
    public function __construct(int $max = 10000)
    {
        $this->max = max(0, $max);
    }

    /**
     * Returns next Fibonacci number (milliseconds).
     */
    public function next(): int
    {
        $delay = $this->curr;

        // Move Fibonacci forward
        [$this->prev, $this->curr] = [$this->curr, $this->prev + $this->curr];

        $this->incrementAttempt();

        return $this->ensureNonNegative($this->capDelay($delay, $this->max));
    }

    /**
     * Reset Fibonacci sequence and attempt counter.
     */
    public function reset(): void
    {
        parent::reset();
        $this->prev = 0;
        $this->curr = 1;
    }
}
