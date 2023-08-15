<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob\Contract;

interface JobInterface
{
    public function attempts(): bool;

    public function getConfirm(): bool;

    public function getExchange(): string;

    public function setJobId(string $jobId): self;

    public function getJobId(): string;

    public function getPoolName(): string;

    public function getRoutingKey(): string;

    public function getTimeout(): int;

    /**
     * @return Result|void
     */
    public function handle();
}
