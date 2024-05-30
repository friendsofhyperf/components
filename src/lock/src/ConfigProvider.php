<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Lock;

use FriendsOfHyperf\Lock\Annotation\BlockableAspect;
use FriendsOfHyperf\Lock\Annotation\LockAspect;
use FriendsOfHyperf\Lock\Listener\RegisterPropertyHandlerListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                RegisterPropertyHandlerListener::class,
            ],
            'aspects' => [
                LockAspect::class,
                BlockableAspect::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of lock.',
                    'source' => __DIR__ . '/../publish/lock.php',
                    'destination' => BASE_PATH . '/config/autoload/lock.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'The migrations file of lock',
                    'source' => __DIR__ . '/../migrations/create_lock_table.php',
                    'destination' => BASE_PATH . '/migrations/2021_01_31_000000_create_lock_table.php',
                ],
            ],
        ];
    }
}
