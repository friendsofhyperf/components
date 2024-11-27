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

    public function hasRun(bool $value): void
    {
        $this->runned = $value;
    }

    public function setHasRun(): bool
    {
        return $this->runned;
    }
}
