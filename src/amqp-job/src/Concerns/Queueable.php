<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AmqpJob\Concerns;

trait Queueable
{
    protected bool $confirm = false;

    protected string $exchange = 'hyperf';

    protected string $jobId = '';

    protected int $maxAttempts = 0;

    protected string $poolName = 'default';

    protected string $routingKey = 'hyperf.job';

    protected int $timeout = 5;

    public function getConfirm(): bool
    {
        return $this->confirm;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function setJobId(string $jobId): self
    {
        $this->jobId = $jobId;
        return $this;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function getPoolName(): string
    {
        return $this->poolName;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
