<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TcpSender;

use FriendsOfHyperf\TcpSender\Listener\InitSenderListener;
use FriendsOfHyperf\TcpSender\Listener\OnPipeMessageListener;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'listeners' => [
                InitSenderListener::class,
                OnPipeMessageListener::class,
            ],
        ];
    }
}