<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'commands' => [
                Command\ClearCommand::class,
                Command\InstallCommand::class,
                Command\PruneCommand::class,
            ],
            'listeners' => [
                Listener\SetRequestLifecycleListener::class,
                Listener\CommandListener::class,
                Listener\DbQueryListener::class,
                Listener\ExceptionHandlerListener::class,
                Listener\SetupTelescopeServerListener::class,
            ],
            'aspects' => [
                Aspect\CoroutineAspect::class,
                Aspect\CacheAspect::class,
                Aspect\EventAspect::class,
                Aspect\GrpcClientAspect::class,
                Aspect\HttpClientAspect::class,
                Aspect\LogAspect::class,
                Aspect\RedisAspect::class,
                Aspect\RpcAspect::class,
                Aspect\RequestDispatcherAspect::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file for hyperf telescope',
                    'source' => __DIR__ . '/../publish/telescope.php',
                    'destination' => BASE_PATH . '/config/autoload/telescope.php',
                ],
                [
                    'id' => 'migrations',
                    'description' => 'The migrations file of hyperf telescope',
                    'source' => __DIR__ . '/../migrations/2020_08_03_064816_telescope_entries.php',
                    'destination' => BASE_PATH . '/migrations/2020_08_03_064816_telescope_entries.php',
                ],
            ],
        ];
    }
}
