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
use Psr\Container\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                EasySms::class => fn (ContainerInterface $container) => $container->get(EasySmsFactory::class),
            ],
            'listener' => [
                RegisterChannelListener::class,
            ],
            'publish' => [
                [
                    'id' => 'easy-sms',
                    'description' => 'easy sms channel configuration',
                    'source' => dirname(__DIR__) . '/publish/easy_sms.php',
                    'target' => BASE_PATH . '/config/autoload/easy_sms.php',
                ],
            ],
        ];
    }
}
