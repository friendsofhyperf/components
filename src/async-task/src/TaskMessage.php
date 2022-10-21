<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AsyncTask;

class TaskMessage
{
    public function __construct(protected TaskInterface $task)
    {
    }

    public function task(): TaskInterface
    {
        return $this->task;
    }

    public function getDelay(): float
    {
        return (int) ($this->task->delay ?? 0);
    }

    public function getMaxAttempts(): int
    {
        return (int) ($this->task->maxAttempts ?? 0);
    }

    public function getRetryAfter(): int
    {
        return (int) ($this->task->retryAfter ?? 0);
    }
}
