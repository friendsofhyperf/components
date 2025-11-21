<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
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
                Aspect\CacheAspect::class,
                Aspect\CoroutineAspect::class,
                Aspect\FilesystemAspect::class,
                Aspect\GuzzleHttpClientAspect::class,
                Aspect\LoggerAspect::class,
                Aspect\SingletonAspect::class,
                Tracing\Aspect\AmqpProducerAspect::class,
                Tracing\Aspect\AsyncQueueJobMessageAspect::class,
                Tracing\Aspect\CacheAspect::class,
                Tracing\Aspect\CoordinatorAspect::class,
                Tracing\Aspect\CoroutineAspect::class,
                Tracing\Aspect\DbAspect::class,
                Tracing\Aspect\ElasticsearchAspect::class,
                Tracing\Aspect\ElasticsearchRequestAspect::class,
                Tracing\Aspect\FilesystemAspect::class,
                Tracing\Aspect\GrpcAspect::class,
                Tracing\Aspect\GuzzleHttpClientAspect::class,
                Tracing\Aspect\KafkaProducerAspect::class,
                Tracing\Aspect\RpcAspect::class,
                Tracing\Aspect\RpcEndpointAspect::class,
                Tracing\Aspect\TraceAnnotationAspect::class,
                Tracing\Aspect\ViewRenderAspect::class,
            ],
            'commands' => [
                Command\AboutCommand::class,
                Command\TestCommand::class,
            ],
            'dependencies' => [
                \Sentry\ClientBuilder::class => Factory\ClientBuilderFactory::class,
                \Sentry\State\HubInterface::class => Factory\HubFactory::class,
                \Sentry\Transport\TransportInterface::class => Transport\CoHttpTransport::class,
            ],
            'listeners' => [
                Listener\SetupSentryListener::class,
                Listener\EventHandleListener::class => PHP_INT_MAX - 1,
                Crons\Listener\EventHandleListener::class => PHP_INT_MAX - 1,
                Tracing\Listener\EventHandleListener::class => PHP_INT_MAX, // !! Make sure it is the first one to handle the event
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
