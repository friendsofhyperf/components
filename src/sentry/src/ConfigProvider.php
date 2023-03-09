<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'aspects' => [
                Aspect\BreadcrumbAspect::class,
                Aspect\GuzzleHttpClientAspect::class,
                Aspect\LoggerAspect::class,
                Aspect\RedisAspect::class,
                Aspect\SingletonAspect::class,
                Aspect\SentryHttpClientFactoryAspect::class,
            ],
            'commands' => [
                Command\TestCommand::class,
            ],
            'dependencies' => [
                \Sentry\ClientBuilderInterface::class => Factory\ClientBuilderFactory::class,
                \Sentry\State\HubInterface::class => Factory\HubFactory::class,
            ],
            'listeners' => [
                Listener\InitHubListener::class,
                Listener\DbQueryListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'class_map' => [
                        \Sentry\SentrySdk::class => __DIR__ . '/../class_map/SentrySdk.php',
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config file for sentry.',
                    'source' => __DIR__ . '/../publish/sentry.php',
                    'destination' => BASE_PATH . '/config/autoload/sentry.php',
                ],
            ],
        ];
    }
}
