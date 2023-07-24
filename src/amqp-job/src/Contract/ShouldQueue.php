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

interface ShouldQueue
{
    public function attempts(): bool;

    public function getMaxAttempts(): int;

    public function getConfirm(): bool;

    public function getExchange(): string;

    public function getPoolName(): string;

    public function getRoutingKey(): string;

    public function getTimeout(): int;
}
