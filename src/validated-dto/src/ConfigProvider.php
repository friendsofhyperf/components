<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__, 2));

        return [
            'commands' => [
                Command\GeneratorCommand::class,
            ],

            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file of dto.',
                    'source' => __DIR__ . '/../publish/dto.php',
                    'destination' => BASE_PATH . '/config/autoload/dto.php',
                ],
            ],
        ];
    }
}
