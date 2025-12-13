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
            'aspects' => [
                Aspect\CacheAspect::class,
                Aspect\CoroutineAspect::class,
                Aspect\EventAspect::class,
                Aspect\GrpcClientAspect::class,
                Aspect\GrpcCoreMiddlewareAspect::class,
                Aspect\GuzzleHttpClientAspect::class,
                Aspect\LogAspect::class,
                Aspect\RedisAspect::class,
                Aspect\RequestDispatcherAspect::class,
                Aspect\RpcAspect::class,
            ],
            'commands' => [
                Command\ClearCommand::class,
                Command\InstallCommand::class,
                Command\PruneCommand::class,
            ],
            'dependencies' => [
                Contract\EntriesRepository::class => Storage\EntriesRepositoryFactory::class,
                Contract\ClearableRepository::class => fn ($container) => $container->get(Contract\EntriesRepository::class),
                Contract\PrunableRepository::class => fn ($container) => $container->get(Contract\EntriesRepository::class),
            ],
            'listeners' => [
                Listener\CommandListener::class,
                Listener\CronEventListener::class,
                Listener\DbQueryListener::class,
                Listener\ExceptionHandlerListener::class,
                Listener\FetchRecordingOnBootListener::class,
                Listener\RedisCommandExecutedListener::class,
                Listener\RegisterRoutesListener::class => -1,
                Listener\RequestHandledListener::class,
                Listener\SetRequestLifecycleListener::class,
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
