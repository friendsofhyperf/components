<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification;

use FriendsOfHyperf\Notification\Contract\Dispatcher;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [
                Command\GenNotificationCommand::class,
                Command\NotificationTableCommand::class,
            ],
            'dependencies' => [
                Dispatcher::class => ChannelManager::class,
            ],
            'listeners' => [
                Listener\RegisterChannelsListener::class,
            ],
        ];
    }
}
