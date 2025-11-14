<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Bus;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Hyperf\Conditionable\Conditionable;
use Hyperf\Context\ApplicationContext;

class PendingAsyncQueueDispatch
{
    use Conditionable;

    public ?string $pool = null;

    public int $delay = 0;

    public function __construct(protected JobInterface $job)
    {
    }

    public function __destruct()
    {
        ApplicationContext::getContainer()
            ->get(DriverFactory::class)
            ->get($this->pool ?? 'default')
            ->push($this->job, $this->delay);
    }

    public function setMaxAttempts(int $maxAttempts): static
    {
        $this->job->setMaxAttempts($maxAttempts);
        return $this;
    }

    public function onPool(string $pool): static
    {
        $this->pool = $pool;
        return $this;
    }

    public function delay(int $delay): static
    {
        $this->delay = $delay;
        return $this;
    }
}
