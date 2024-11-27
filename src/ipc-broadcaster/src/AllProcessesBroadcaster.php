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

use function Hyperf\Support\class_uses_recursive;

class AllProcessesBroadcaster implements BroadcasterInterface
{
    public function __construct(
        protected ServerBroadcaster $serverBroadcaster,
        protected UserProcessesBroadcaster $userProcessesBroadcaster
    ) {
    }

    public function broadcast(IpcMessageInterface $message): void
    {
        /** @var IpcMessageInterface|mixed $message */
        if (
            in_array(Traits\RunsInCurrentWorker::class, class_uses_recursive($message))
            && ! $message->isRunned()
        ) {
            $message->handle();
            $message->setRunned(true);
        }

        if (Constant::isCoroutineServer()) {
            return;
        }

        $this->serverBroadcaster->broadcast($message);
        $this->userProcessesBroadcaster->broadcast($message);
    }
}
