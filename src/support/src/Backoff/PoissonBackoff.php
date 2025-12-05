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
    private int $mean;     // 平均延迟

    private int $max;

    private int $attempt = 0;

    public function __construct(int $mean = 100, int $max = 5000)
    {
        $this->mean = max(0, $mean);  // 确保均值不为负数
        $this->max = $max;
    }

    public function next(): int
    {
        // 生成泊松分布随机数
        // 对于大均值，使用正态近似以避免数值下溢
        if ($this->mean > 700) {
            // 对于大均值，泊松分布可以用正态分布近似
            // 使用 Box-Muller 变换生成正态分布随机数
            $delay = (int) round($this->mean + sqrt($this->mean) * $this->gaussRandom());
        } else {
            // 对于中小均值，使用改进的 Knuth 算法
            // 对于较大的均值，使用对数方法避免下溢
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

        // 确保延迟不为负数
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
     * 使用 Knuth 算法生成泊松分布（适用于小均值）.
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
     * 使用对数方法生成泊松分布（适用于中等均值）.
     */
    private function generatePoissonLarge(): int
    {
        // 对于中等均值，使用更简单的算法避免复杂计算
        // 使用截断的正态分布作为泊松分布的近似
        $result = (int) round($this->mean + sqrt($this->mean) * $this->gaussRandom());
        return max(0, $result);
    }

    /**
     * 生成标准正态分布随机数（Box-Muller 变换）.
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
