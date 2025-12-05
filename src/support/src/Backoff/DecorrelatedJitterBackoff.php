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
 * Implements the "decorrelated jitter" backoff strategy as recommended by AWS for robust retry logic.
 *
 * Decorrelated jitter is a backoff algorithm designed to mitigate the "thundering herd" problem,
 * where many clients retrying at the same time can overwhelm a system. Unlike standard exponential
 * backoff or simple jitter, decorrelated jitter randomizes the delay for each retry attempt in a way
 * that both increases the delay over time and ensures that retries are spread out, reducing the chance
 * of synchronized retries.
 *
 * ## Why AWS Recommends Decorrelated Jitter
 * AWS recommends this approach because it provides better distribution of retry attempts across clients,
 * leading to improved system stability under load. By decorrelating the retry intervals, it prevents
 * large numbers of clients from retrying simultaneously after a failure, which can cause cascading failures.
 *
 * ## How It Works
 * The algorithm works as follows:
 *   - On each retry, the next delay is chosen randomly between a base value and the previous delay multiplied by a factor.
 *   - The delay is capped at a maximum value.
 *   - This approach "decorrelates" the retry intervals, so each client follows a unique retry pattern.
 *
 * Formula (per AWS best-practice):
 *   nextDelay = random_between(base, prevDelay * factor)
 *   nextDelay = min(nextDelay, max)
 *
 * ## Difference from Standard Exponential Backoff with Jitter
 * - Standard exponential backoff increases the delay exponentially, sometimes with added jitter (randomness).
 * - Decorrelated jitter uses the previous delay as part of the calculation, so the growth is less predictable and more randomized.
 * - This further reduces the risk of synchronized retries compared to simple exponential backoff with jitter.
 *
 * ## When to Use
 * Use decorrelated jitter backoff when:
 *   - You are building distributed systems or clients that may experience simultaneous failures.
 *   - You want to minimize the risk of thundering herd problems.
 *   - You need robust, production-grade retry logic as recommended by AWS.
 *
 * For simple or low-traffic scenarios, linear or fixed backoff may suffice. For high-availability or cloud-native
 * systems, decorrelated jitter is preferred.
 *
 * @see https://aws.amazon.com/blogs/architecture/exponential-backoff-and-jitter/
 * @see https://github.com/awslabs/aws-sdk-rust/blob/main/sdk/aws-smithy-async/src/backoff.rs
 */
class DecorrelatedJitterBackoff extends AbstractBackoff
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
     * Constructor.
     *
     * @param positive-int $base Minimum delay in ms
     * @param positive-int $max Cap delay in ms
     * @param float $factor Multiplier (default 3 per AWS best-practice)
     */
    public function __construct(
        int $base = 100,
        int $max = 10000,
        float $factor = 3.0
    ) {
        $this->validateParameters($base, $max, $factor);

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
        // Handle edge case where max is negative or zero
        if ($this->max <= 0) {
            $this->incrementAttempt();
            return 0;
        }

        // Handle edge case where factor is 0
        if ($this->factor == 0) {
            $delay = $this->base;
            $this->prevDelay = $delay;
            $this->incrementAttempt();
            return $this->capDelay($delay, $this->max);
        }

        // Compute upper bound with overflow protection
        $upper = $this->prevDelay * $this->factor;

        // Protect against integer overflow
        if ($upper > PHP_INT_MAX) {
            $upper = PHP_INT_MAX;
        }

        // Cast to int after overflow check
        $upper = (int) $upper;

        // Ensure upper bound is at least base to avoid random_int errors
        $upper = max($upper, $this->base);

        // Random value between base and upper bound
        $delay = random_int($this->base, $upper);

        // Cap by max
        $delay = $this->capDelay($delay, $this->max);

        // Update memory
        $this->prevDelay = $delay;

        $this->incrementAttempt();

        return $delay;
    }

    /**
     * Reset attempt and history.
     */
    public function reset(): void
    {
        parent::reset();
        $this->prevDelay = $this->base;
    }
}
