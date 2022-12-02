<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Tinker;

class ConfigProvider
{
    public function __invoke(): array
    {
        // fix for IDE
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'commands' => [
                Command\TinkerCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for tinker.',
                    'source' => __DIR__ . '/../publish/tinker.php',
                    'destination' => BASE_PATH . '/config/autoload/tinker.php',
                ],
            ],
        ];
    }
}
