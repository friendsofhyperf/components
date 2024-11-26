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

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => [
                Contract\BroadcasterInterface::class => AllProcessesBroadcaster::class,
            ],
            'listeners' => [
                Listener\OnPipeMessageListener::class,
                Listener\SetConstantOnBootListener::class,
            ],
        ];
    }
}
