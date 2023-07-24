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

use FriendsOfHyperf\AmqpJob\Contract\Attempt;
use Hyperf\Amqp\Message\Type;
use Hyperf\Context\ApplicationContext;

trait Queueable
{
    protected int $attempts = 0;

    protected bool $confirm = false;

    protected string $exchange = 'hyperf';

    protected string $jobId = '';

    protected int $maxAttempts = 0;

    protected string $poolName = 'default';

    protected string $routingKey = 'hyperf.job';

    protected int $timeout = 5;

    public function attempts(): bool
    {
        $attempt = ApplicationContext::getContainer()->get(Attempt::class);
        $this->attempts = (int) $attempt->incr($this->getJobId());

        if ($this->getMaxAttempts() > $this->attempts) {
            return true;
        }

        return false;
    }

    public function clearAttempts(): void
    {
        $attempt = ApplicationContext::getContainer()->get(Attempt::class);
        $attempt->clear($this->getJobId());
    }

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

    public function getType(): string|Type
    {
        return Type::DIRECT;
    }
}
