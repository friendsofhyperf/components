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
 *
 * @param int $mean The mean delay (in milliseconds) for the Poisson distribution. Default is 500 ms.
 * @param int $max The maximum allowed delay (in milliseconds). Default is 5000 ms.
 */
class PoissonBackoff implements BackoffInterface
{
    private int $mean;     // Average delay

    private int $max;

    private int $attempt = 0;

    public function __construct(int $mean = 100, int $max = 5000)
    {
        $this->mean = max(0, $mean);  // Ensure mean is not negative
        $this->max = $max;
    }

    public function next(): int
    {
        // Generate Poisson distributed random number
        // For large means, use normal approximation to avoid numerical underflow
        if ($this->mean > 700) {
            // For large means, Poisson distribution can be approximated with normal distribution
            // Use Box-Muller transform to generate normal distributed random numbers
            $delay = (int) round($this->mean + sqrt($this->mean) * $this->gaussRandom());
        } else {
            // For small to medium means, use improved Knuth algorithm
            // For larger means, use logarithmic method to avoid underflow
            if ($this->mean > 30) {
                $delay = $this->generatePoissonLarge();
            } else {
                $delay = $this->generatePoissonKnuth();
            }
        }

        ++$this->attempt;

        if ($delay > $this->max) {
            $delay = $this->max;
        }

        // Ensure delay is not negative
        return max(0, $delay);
    }

    public function reset(): void
    {
        $this->attempt = 0;
    }

    public function getAttempt(): int
    {
        return $this->attempt;
    }

    /**
     * Generate Poisson distribution using Knuth algorithm (suitable for small means).
     */
    private function generatePoissonKnuth(): int
    {
        $L = exp(-$this->mean);
        $k = 0;
        $p = 1.0;

        do {
            ++$k;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $L);

        return $k - 1;
    }

    /**
     * Generate Poisson distribution using logarithmic method (suitable for medium means).
     */
    private function generatePoissonLarge(): int
    {
        // For medium means, use a simpler algorithm to avoid complex calculations
        // Use truncated normal distribution as an approximation of Poisson distribution
        $result = (int) round($this->mean + sqrt($this->mean) * $this->gaussRandom());
        return max(0, $result);
    }

    /**
     * Generate standard normal distributed random number (Box-Muller transform).
     */
    private function gaussRandom(): float
    {
        static $hasSpare = false;
        static $spare = 0.0;

        if ($hasSpare) {
            $hasSpare = false;
            return $spare;
        }
        $hasSpare = true;
        $u = 2.0 * mt_rand() / mt_getrandmax() - 1.0;
        $v = 2.0 * mt_rand() / mt_getrandmax() - 1.0;
        $s = $u * $u + $v * $v;

        while ($s >= 1.0 || $s == 0.0) {
            $u = 2.0 * mt_rand() / mt_getrandmax() - 1.0;
            $v = 2.0 * mt_rand() / mt_getrandmax() - 1.0;
            $s = $u * $u + $v * $v;
        }

        $s = sqrt(-2.0 * log($s) / $s);
        $spare = $v * $s;
        return $u * $s;
    }
}
