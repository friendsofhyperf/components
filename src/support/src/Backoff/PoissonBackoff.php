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
 * @param int $max  The maximum allowed delay (in milliseconds). Default is 5000 ms.
 */
class PoissonBackoff implements BackoffInterface
{
    private int $mean;     // 平均延迟

    private int $max;

    private int $attempt = 0;

    public function __construct(int $mean = 500, int $max = 5000)
    {
        $this->mean = $mean;
        $this->max = $max;
    }

    public function next(): int
    {
        // 泊松生成 (Knuth算法)
        $L = exp(-$this->mean);
        $k = 0;
        $p = 1.0;

        do {
            ++$k;
            $p *= mt_rand() / mt_getrandmax();
        } while ($p > $L);

        $delay = ($k - 1);

        ++$this->attempt;

        if ($delay > $this->max) {
            $delay = $this->max;
        }
        return $delay;
    }

    public function reset(): void
    {
        $this->attempt = 0;
    }

    public function getAttempt(): int
    {
        return $this->attempt;
    }
}
