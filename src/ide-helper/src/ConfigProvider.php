<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://github.com/friendsofhyperf/ide-helper
 * @document https://github.com/friendsofhyperf/ide-helper/blob/master/README.md
 * @contact  huangdijia@gmail.com
 * @license  https://github.com/friendsofhyperf/ide-helper/blob/master/LICENSE
 */
namespace FriendsOfHyperf\IdeHelper;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
                Command\Model::class,
                Command\Macro::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
