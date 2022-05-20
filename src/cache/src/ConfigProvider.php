<?php

declare(strict_types=1);
/**
 * This file is part of cache.
 *
 * @link     https://github.com/friendsofhyperf/cache
 * @document https://github.com/friendsofhyperf/cache/blob/2.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                CacheInterface::class => CacheFactory::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
            'listeners' => [],
            'publish' => [],
        ];
    }
}
