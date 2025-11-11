<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncQueueClosureJob;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\Job;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;

class PendingClosureDispatch
{
    use Conditionable;

    protected string $queue = 'default';

    protected int $delay = 0;

    public function __construct(protected Job $job)
    {
    }

    public function __destruct()
    {
        ApplicationContext::getContainer()
            ->get(DriverFactory::class)
            ->get($this->queue)
            ->push($this->job, $this->delay);
    }

    public function setMaxAttempts(int $maxAttempts): static
    {
        $this->job->setMaxAttempts($maxAttempts);
        return $this;
    }

    public function onQueue(string $queue): static
    {
        $this->queue = $queue;
        return $this;
    }

    public function delay(int $delay): static
    {
        $this->delay = $delay;
        return $this;
    }
}
