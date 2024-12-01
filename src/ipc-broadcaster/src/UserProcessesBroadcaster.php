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

use function Hyperf\Support\class_uses_recursive;

class UserProcessesBroadcaster implements BroadcasterInterface
{
    public function __construct(protected ?string $name = null, protected ?int $id = null)
    {
    }

    public function broadcast(IpcMessageInterface $message): void
    {
        /** @var IpcMessageInterface|mixed $message */
        if (
            in_array(Traits\RunsInCurrentWorker::class, class_uses_recursive($message))
            && ! $message->hasRun()
        ) {
            $message->handle();
            $message->setHasRun(true);
        }

        if (Constant::isCoroutineServer()) {
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

        if ($processes) {
            $serializeMessage = serialize($message);
            foreach ($processes as $process) {
                $process->write($serializeMessage);
            }
        }
    }
}
