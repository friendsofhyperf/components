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
