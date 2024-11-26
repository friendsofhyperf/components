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

use Closure;
use FriendsOfHyperf\IpcBroadcaster\Contract\BroadcasterInterface;
use FriendsOfHyperf\IpcBroadcaster\Contract\IpcMessageInterface;
use Hyperf\Context\ApplicationContext;

function broadcast(IpcMessageInterface|Closure $message)
{
    if ($message instanceof Closure) {
        $message = new ClosureIpcMessage($message);
    }

    $container = ApplicationContext::getContainer();
    $broadcaster = $container->get(BroadcasterInterface::class);
    $broadcaster->broadcast($message);
}
