<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Purifier;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'config file of purifier.',
                    'source' => __DIR__ . '/../publish/purifier.php',
                    'destination' => BASE_PATH . '/config/autoload/purifier.php',
                ],
            ],
        ];
    }
}
