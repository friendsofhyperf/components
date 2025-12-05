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
 * Implements a Poisson-distributed backoff strategy for retrying operations.
 *
 * Poisson backoff introduces randomized delays between retries, where each delay is drawn from a Poisson distribution.
 * This approach is useful for reducing the likelihood of synchronized retries (the "thundering herd" problem) in distributed systems,
 * as it introduces natural jitter and unpredictability to the retry intervals.
 *
 * Unlike linear or exponential backoff, which increase delays in a predictable manner, Poisson backoff produces delays
 * that are randomly distributed around a specified mean, making it harder for multiple clients to collide on retry timing.
 *
 * The delay is generated using the Knuth algorithm for Poisson random number generation.
 *
 * @see https://en.wikipedia.org/wiki/Poisson_distribution
 * @see https://en.wikipedia.org/wiki/Knuth%27s_algorithm
 */
class PoissonBackoff extends AbstractBackoff
{
    /**
     * @var int The mean delay (in milliseconds) for the Poisson distribution. Default is 500 ms.
     */
    private int $mean;     // Average delay

    /**
     * @var int The maximum allowed delay (in milliseconds). Default is 5000 ms.
     */
    private int $max;

    private bool $hasSpare = false;

    private float $spare = 0.0;

    private static ?float $maxRandValue = null;

    /**
     * @param positive-int $mean
     * @param positive-int $max
     */
    public function __construct(int $mean = 100, int $max = 5000)
    {
        $this->validateParameters($mean, $max);

        // Store original values for potential debugging
        $this->mean = $mean;
        $this->max = $max;

        // Initialize cached max random value
        if (self::$maxRandValue === null) {
            self::$maxRandValue = mt_getrandmax();
        }
    }

    public function next(): int
    {
        // Handle edge cases
        if ($this->max <= 0) {
            $this->incrementAttempt();
            return 0;
        }

        $effectiveMean = max(0, $this->mean);

        // Generate Poisson distributed random number
        // For large means, use normal approximation to avoid numerical underflow
        if ($effectiveMean > 700) {
            // For large means, Poisson distribution can be approximated with normal distribution
            // Use Box-Muller transform to generate normal distributed random numbers
            $delay = (int) round($effectiveMean + sqrt($effectiveMean) * $this->gaussRandom());
        } else {
            // For small to medium means, use improved Knuth algorithm
            // For larger means, use logarithmic method to avoid underflow
            if ($effectiveMean > 30) {
                $delay = $this->generatePoissonLarge($effectiveMean);
            } else {
                $delay = $this->generatePoissonKnuth($effectiveMean);
            }
        }

        $this->incrementAttempt();

        // Cap delay and ensure non-negative
        return $this->ensureNonNegative(
            $this->capDelay($delay, $this->max)
        );
    }

    /**
     * Generate Poisson distribution using Knuth algorithm (suitable for small means).
     */
    private function generatePoissonKnuth(float $mean): int
    {
        $L = exp(-$mean);
        $k = 0;
        $p = 1.0;

        // Safety check to prevent infinite loops
        $maxIterations = min(1000, (int) ($mean * 10) + 100);

        do {
            ++$k;
            $u = mt_rand() / self::$maxRandValue;

            // Check for potential underflow
            if ($u <= 0.0) {
                break;
            }

            $p *= $u;

            // Additional safety check
            if ($p <= 0.0 || $k > $maxIterations) {
                // Fallback to mean if we encounter numerical issues
                return (int) $mean;
            }
        } while ($p > $L);

        return $k - 1;
    }

    /**
     * Generate Poisson distribution using logarithmic method (suitable for medium means).
     */
    private function generatePoissonLarge(float $mean): int
    {
        // For medium means, use a simpler algorithm to avoid complex calculations
        // Use truncated normal distribution as an approximation of Poisson distribution

        $stdDev = sqrt($mean);
        $result = $mean + $stdDev * $this->gaussRandom();

        // Ensure result is within reasonable bounds (3 standard deviations)
        $minResult = max(0, $mean - 3 * $stdDev);
        $maxResult = $mean + 3 * $stdDev;

        $result = max($minResult, min($result, $maxResult));

        return (int) round($result);
    }

    /**
     * Generate standard normal distributed random number (Box-Muller transform).
     */
    private function gaussRandom(): float
    {
        if ($this->hasSpare) {
            $this->hasSpare = false;
            return $this->spare;
        }

        $this->hasSpare = true;

        // Use cached maxRandValue for better performance
        $maxValue = self::$maxRandValue;

        do {
            $u = 2.0 * mt_rand() / $maxValue - 1.0;
            $v = 2.0 * mt_rand() / $maxValue - 1.0;
            $s = $u * $u + $v * $v;
        } while ($s >= 1.0 || $s == 0.0);

        $s = sqrt(-2.0 * log($s) / $s);
        $this->spare = $v * $s;
        return $u * $s;
    }
}
