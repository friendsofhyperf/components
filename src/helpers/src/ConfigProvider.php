<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/helpers.
 *
 * @link     https://github.com/friendsofhyperf/helpers
 * @document https://github.com/friendsofhyperf/helpers/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [],
        ];
    }
}
