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

class AllProcessesBroadcaster implements BroadcasterInterface
{
    public function __construct(
        protected ServerBroadcaster $serverBroadcaster,
        protected UserProcessesBroadcaster $userProcessesBroadcaster
    ) {
    }

    public function broadcast(IpcMessageInterface $message): void
    {
        if (Constant::isCoroutineServer()) {
            $message->handle();
            return;
        }

        $this->serverBroadcaster->broadcast($message);
        $this->userProcessesBroadcaster->broadcast($message);
    }
}
