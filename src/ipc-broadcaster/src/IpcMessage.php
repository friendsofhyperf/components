<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster;

abstract class IpcMessage implements Contract\IpcMessageInterface, Contract\CanBeSetOrGetFromWorkerId
{
    protected int $fromWorkerId = 0;

    public function setFromWorkerId(int $fromWorkerId): void
    {
        $this->fromWorkerId = $fromWorkerId;
    }

    public function getFromWorkerId(): int
    {
        return $this->fromWorkerId;
    }

    abstract public function handle(): void;
}
