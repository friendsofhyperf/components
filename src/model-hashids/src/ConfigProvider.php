<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelHashids;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of hashids.',
                    'source' => __DIR__ . '/../publish/hashids.php',
                    'destination' => BASE_PATH . '/config/autoload/hashids.php',
                ],
            ],
        ];
    }
}
