<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConsoleSpinner;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of console-spinner.',
                    'source' => __DIR__ . '/../publish/console_spinner.php',
                    'destination' => BASE_PATH . '/config/autoload/console_spinner.php',
                ],
            ],
        ];
    }
}
