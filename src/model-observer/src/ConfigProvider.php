<?php

declare(strict_types=1);
/**
 * This file is part of model-observer.
 *
 * @link     https://github.com/friendsofhyperf/model-observer
 * @document https://github.com/friendsofhyperf/model-observer/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelObserver;

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
            'listeners' => [],
            'publish' => [],
        ];
    }
}
