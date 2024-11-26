<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster\Listener;

use FriendsOfHyperf\IpcBroadcaster\ServerType;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Event\MainCoroutineServerStart;

class SetServerTypeOnBootListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            BeforeProcessHandle::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        ServerType::setCoroutineServer(true);
    }
}
