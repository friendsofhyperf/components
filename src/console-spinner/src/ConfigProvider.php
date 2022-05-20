<?php

declare(strict_types=1);
/**
 * This file is part of console-spinner.
 *
 * @link     https://github.com/friendsofhyperf/console-spinner
 * @document https://github.com/friendsofhyperf/console-spinner/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConsoleSpinner;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'dependencies' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
            'listeners' => [
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'config file of console-spinner.',
                    'source' => __DIR__ . '/../publish/console_spinner.php',
                    'destination' => BASE_PATH . '/config/console_spinner.php',
                ],
            ],
        ];
    }
}
