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

class FibonacciBackoff implements BackoffInterface
{
    /**
     * @var int Maximum allowed delay (milliseconds)
     */
    private int $max;

    /**
     * @var int Current retry attempt number
     */
    private int $attempt = 0;

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
     * @param int $max Maximum cap delay in milliseconds
     */
    public function __construct(int $max = 10000)
    {
        $this->max = $max;
    }

    /**
     * Returns next Fibonacci number (milliseconds).
     */
    public function next(): int
    {
        $delay = $this->curr;

        // Move Fibonacci forward
        [$this->prev, $this->curr] = [$this->curr, $this->prev + $this->curr];

        ++$this->attempt;

        if ($delay > $this->max) {
            $delay = $this->max;
        }

        return $delay;
    }

    /**
     * Reset Fibonacci sequence and attempt counter.
     */
    public function reset(): void
    {
        $this->attempt = 0;
        $this->prev = 0;
        $this->curr = 1;
    }

    /**
     * 1-based attempt count.
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
