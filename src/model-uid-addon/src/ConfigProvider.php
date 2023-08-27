<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ModelUidAddon;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        'Hyperf\Database\Schema\ForeignIdColumnDefinition' => __DIR__ . '/../class_map/Hyperf/Database/Schema/ForeignIdColumnDefinition.php',
                        'Hyperf\Database\Model\Concerns\HasUlids' => __DIR__ . '/../class_map/Hyperf/Database/Model/Concerns/HasUlids.php',
                        'Hyperf\Database\Model\Concerns\HasUuids' => __DIR__ . '/../class_map/Hyperf/Database/Model/Concerns/HasUuids.php',
                    ],
                ],
            ],
            'listeners' => [
                Listener\CreatingListener::class,
                Listener\RegisterMixinListener::class,
            ],
        ];
    }
}
