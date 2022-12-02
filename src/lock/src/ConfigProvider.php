<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'aspects' => [
                Annotation\LockAspect::class,
            ],
            'listeners' => [
                Listener\RegisterPropertyHandlerListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of lock.',
                    'source' => __DIR__ . '/../publish/lock.php',
                    'destination' => BASE_PATH . '/config/autoload/lock.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'The migrations of lock',
                    'source' => __DIR__ . '/../migrations/create_lock_table.php',
                    'destination' => BASE_PATH . '/migrations/2021_01_31_000000_create_lock_table.php',
                ],
            ],
        ];
    }
}
