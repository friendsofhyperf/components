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
    protected string $exchange = 'hyperf';

    protected string $routingKey = 'hyperf.job';

    protected string $poolName = 'default';

    protected bool $confirm = false;

    protected int $timeout = 5;

    protected int $attempts = 0;

    protected int $maxAttempts = 0;

    public function attempts(): bool
    {
        if ($this->getMaxAttempts() > $this->attempts++) {
            return true;
        }
        return false;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function getExchange(): string
    {
        return $this->exchange;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }

    public function getConfirm(): bool
    {
        return $this->confirm;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getPoolName(): string
    {
        return $this->poolName;
    }
}
