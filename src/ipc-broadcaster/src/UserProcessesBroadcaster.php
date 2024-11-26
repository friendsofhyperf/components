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

use FriendsOfHyperf\IpcBroadcaster\Contract\BroadcasterInterface;
use FriendsOfHyperf\IpcBroadcaster\Contract\IpcMessageInterface;
use Hyperf\Process\ProcessCollector;

class UserProcessesBroadcaster implements BroadcasterInterface
{
    public function __construct(protected ?string $name = null, protected ?int $id = null)
    {
    }

    public function broadcast(IpcMessageInterface $message): void
    {
        if (Constant::isCoroutineServer()) {
            $message->handle();
            return;
        }

        if ($this->id !== null) {
            $processes = ProcessCollector::get($this->name);
            $processes[$this->id]->write(serialize($message));
            return;
        }

        if ($this->name !== null) {
            $processes = ProcessCollector::get($this->name);
        } else {
            $processes = ProcessCollector::all();
        }

        foreach ($processes as $process) {
            $process->write(serialize($message));
        }
    }
}
