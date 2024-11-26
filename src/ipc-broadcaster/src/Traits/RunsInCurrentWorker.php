<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster\Traits;

trait RunsInCurrentWorker
{
    protected bool $runned = false;

    public function setRunned(bool $runned): void
    {
        $this->runned = $runned;
    }

    public function isRunned(): bool
    {
        return $this->runned;
    }
}
