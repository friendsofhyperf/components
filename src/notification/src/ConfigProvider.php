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
                Contract\Dispatcher::class => fn ($container) => $container->get(ChannelManager::class),
            ],
            'listeners' => [
                Listener\RegisterChannelsListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for notification.',
                    'source' => __DIR__ . '/../publish/notification.php',
                    'destination' => BASE_PATH . '/config/autoload/notification.php',
                ],
            ],
        ];
    }
}
