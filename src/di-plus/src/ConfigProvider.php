<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\DiPlus;

use Hyperf\Di\Definition\DefinitionSource;
use Hyperf\Di\Definition\Reference;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        DefinitionSource::class => __DIR__ . '/../class_map/DefinitionSource.php',
                        Reference::class => __DIR__ . '/../class_map/Reference.php',
                    ],
                ],
            ],
            'listeners' => [
                Listener\RegisterInjectPropertyListener::class,
            ],
        ];
    }
}
