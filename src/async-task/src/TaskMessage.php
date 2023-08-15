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

class TaskMessage
{
    public function __construct(protected TaskInterface $task)
    {
    }

    public function task(): TaskInterface
    {
        return $this->task;
    }

    public function getDelay(): int
    {
        return $this->task->getDelay();
    }

    public function getMaxAttempts(): int
    {
        return $this->task->getMaxAttempts();
    }

    public function getRetryAfter(): int
    {
        return $this->task->getRetryAfter();
    }
}
