<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask;

abstract class AbstractTask implements TaskInterface
{
    protected int $delay = 0;

    protected int $maxAttempts = 0;

    protected int $retryAfter = 0;

    abstract public function handle(): void;

    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }

    public function getDelay(): int
    {
        return $this->delay;
    }

    public function setMaxAttempts(int $maxAttempts): void
    {
        $this->maxAttempts = $maxAttempts;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function setRetryAfter(int $retryAfter): void
    {
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
