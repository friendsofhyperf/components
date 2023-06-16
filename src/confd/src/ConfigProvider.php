<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd;

use FriendsOfHyperf\Confd\Command\EnvCommand;
use FriendsOfHyperf\Confd\Listener\WatchOnBootListener;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'commands' => [
                EnvCommand::class,
            ],
            'listeners' => [
                WatchOnBootListener::class,
            ],
            'publish' => [
                [
                    'id' => 'confd',
                    'description' => 'The configuration file for confd.',
                    'source' => __DIR__ . '/../publish/confd.php',
                    'destination' => BASE_PATH . '/config/autoload/confd.php',
                ],
            ],
        ];
    }
}
