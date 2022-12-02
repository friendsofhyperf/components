<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'listeners' => [
                Listener\RegisterCommandListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of closure-command.',
                    'source' => __DIR__ . '/../publish/console.php',
                    'destination' => BASE_PATH . '/config/console.php',
                ],
            ],
        ];
    }
}
