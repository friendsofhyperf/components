<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Elasticsearch;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for elasticsearch.',
                    'source' => __DIR__ . '/../publish/elasticsearch.php',
                    'destination' => BASE_PATH . '/config/autoload/elasticsearch.php',
                ],
            ],
        ];
    }
}
