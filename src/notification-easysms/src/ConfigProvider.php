<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\EasySms;

use FriendsOfHyperf\Notification\EasySms\Listener\RegisterChannelListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EasySms::class => EasySmsFactory::class,
            ],
            'listeners' => [
                RegisterChannelListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'easy sms channel configuration',
                    'source' => dirname(__DIR__) . '/publish/easy_sms.php',
                    'target' => BASE_PATH . '/config/autoload/easy_sms.php',
                ],
            ],
        ];
    }
}
