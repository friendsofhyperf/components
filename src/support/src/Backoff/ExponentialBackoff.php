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
 * Exponential Backoff (with Jitter support).
 *
 * Strategy:
 *  - Initial delay: initial
 *  - Exponential growth on each retry (initial * factor^attempt)
 *  - Cap at max value
 *  - Optional jitter to prevent cluster synchronization
 */
class ExponentialBackoff implements BackoffInterface
{
    /**
     * @var int Initial delay (milliseconds)
     */
    private int $initial;

    /**
     * @var int Maximum delay (milliseconds)
     */
    private int $max;

    /**
     * @var float Exponential backoff factor
     */
    private float $factor;

    /**
     * @var bool Whether to enable jitter (randomization)
     */
    private bool $jitter;

    /**
     * @var int Current retry count
     */
    private int $attempt = 0;

    /**
     * Constructor.
     *
     * @param positive-int $initial Initial delay (default 100ms)
     * @param positive-int $max Maximum delay (default 10 seconds)
     * @param float $factor Exponential backoff factor (default 2, meaning multiply by 2)
     * @param bool $jitter Whether to enable jitter
     */
    public function __construct(
        int $initial = 100,
        int $max = 10000,
        float $factor = 2.0,
        bool $jitter = true
    ) {
        $this->initial = $initial;
        $this->max = $max;
        $this->factor = $factor;
        $this->jitter = $jitter;
    }

    /**
     * Get next delay (milliseconds).
     */
    public function next(): int
    {
        $delay = (int) ($this->initial * ($this->factor ** $this->attempt));

        ++$this->attempt;

        // Limit to maximum value
        if ($delay > $this->max) {
            $delay = $this->max;
        }

        // Add jitter (important: prevent concurrent avalanche)
        if ($this->jitter) {
            $delay = random_int((int) ($delay / 2), $delay);
        }

        return $delay;
    }

    /**
     * Reset retry.
     */
    public function reset(): void
    {
        $this->attempt = 0;
    }

    /**
     * Current retry count (0-based; 0 before first call to next()).
     */
    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
