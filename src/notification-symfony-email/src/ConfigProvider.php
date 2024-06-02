<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Mailer;

use FriendsOfHyperf\Notification\Symfony\Mailer\Listener\RegisterChannelListener;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'listeners' => [
                RegisterChannelListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for mail.',
                    'source' => __DIR__ . '/../publish/mail.php',
                    'destination' => BASE_PATH . '/config/autoload/symfony/mail.php',
                ],
            ],
        ];
    }
}
