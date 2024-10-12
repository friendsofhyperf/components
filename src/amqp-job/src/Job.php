<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob;

use FriendsOfHyperf\AmqpJob\Annotation\AmqpJob;
use FriendsOfHyperf\AmqpJob\Contract\Attempt;
use FriendsOfHyperf\AmqpJob\Contract\JobInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Throwable;

abstract class Job implements JobInterface
{
    /**
     * @deprecated since v3.1, will remove in v4.0, use `#[AmqpJob] annotation instead.
     */
    protected ?bool $confirm = null;

    /**
     * @deprecated since v3.1, will remove in v4.0, use `#[AmqpJob] annotation instead.
     */
    protected ?string $exchange = null;

    /**
     * @deprecated since v3.1, will remove in v4.0, use `#[AmqpJob] annotation instead.
     */
    protected ?string $poolName = null;

    /**
     * @deprecated since v3.1, will remove in v4.0, use `#[AmqpJob] annotation instead.
     */
    protected ?string $routingKey = null;

    /**
     * @deprecated since v3.1, will remove in v4.0, use `#[AmqpJob] annotation instead.
     */
    protected ?int $timeout = null;

    /**
     * @deprecated since v3.1, will remove in v4.0, use `#[AmqpJob] annotation instead.
     */
    protected ?int $maxAttempts = null;

    protected string $jobId = '';

    protected ?AmqpJob $annotation = null;

    public function setJobId(string $jobId): self
    {
        $this->jobId = $jobId;
        return $this;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getConfirm(): bool
    {
        return $this->confirm ?? $this->getAnnotation()?->confirm ?? false;
    }

    public function getExchange(): string
    {
        return $this->exchange ?? $this->getAnnotation()?->exchange ?? 'hyperf';
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey ?? $this->getAnnotation()?->routingKey ?? 'hyperf.job';
    }

    public function getPoolName(): string
    {
        return $this->poolName ?? $this->getAnnotation()?->pool ?? null;
    }

    public function getTimeout(): int
    {
        return $this->timeout ?? $this->getAnnotation()?->timeout ?? 5;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts ?? $this->getAnnotation()?->maxAttempts ?? 0;
    }

    public function attempts(): bool
    {
        if ($this->getMaxAttempts() <= 0) {
            return false;
        }

        $attempts = $this->getAttempt()->increment($this->getJobId());

        return $this->getMaxAttempts() > $attempts;
    }

    /**
     * @return \Hyperf\Amqp\Result|string|void
     */
    abstract public function handle();

    public function fail(Throwable $e): void
    {
    }

    protected function getAttempt(): Attempt
    {
        return ApplicationContext::getContainer()->get(Attempt::class);
    }

    protected function getAnnotation(): ?AmqpJob
    {
        return AnnotationCollector::getClassAnnotation(static::class, AmqpJob::class);
    }
}
