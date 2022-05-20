<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/macros.
 *
 * @link     https://github.com/friendsofhyperf/macros
 * @document https://github.com/friendsofhyperf/macros/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros;

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
