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

class DecorrelatedJitterBackoff implements BackoffInterface
{
    /**
     * @var int Minimal starting delay (milliseconds)
     */
    private int $base;

    /**
     * @var int Maximum allowed delay (milliseconds)
     */
    private int $max;

    /**
     * @var float Growth multiplier (similar to exponential backoff)
     */
    private float $factor;

    /**
     * @var int Last delay result (for correlation step)
     */
    private int $prevDelay;

    /**
     * @var int Current retry attempt
     */
    private int $attempt = 0;

    /**
     * Constructor.
     *
     * @param int $base Minimum delay in ms
     * @param int $max Cap delay in ms
     * @param float $factor Multiplier (default 3 per AWS best-practice)
     */
    public function __construct(
        int $base = 100,
        int $max = 10000,
        float $factor = 3.0
    ) {
        $this->base = $base;
        $this->max = $max;
        $this->factor = $factor;
        $this->prevDelay = $base;
    }

    /**
     * Decorrelated jitter based on AWS best-practice.
     */
    public function next(): int
    {
        // Compute upper bound
        $upper = (int) ($this->prevDelay * $this->factor);

        // Random value between base and upper bound
        $delay = random_int($this->base, $upper);

        // Cap by max
        $delay = min($delay, $this->max);

        // Update memory
        $this->prevDelay = $delay;

        ++$this->attempt;

        return $delay;
    }

    /**
     * Reset attempt and history.
     */
    public function reset(): void
    {
        $this->attempt = 0;
        $this->prevDelay = $this->base;
    }

    /**
     * 1-based attempt index.
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
