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
                Aspect\GuzzleHttpClientAspect::class,
                Aspect\LoggerAspect::class,
                Aspect\RedisAspect::class,
                Aspect\SingletonAspect::class,
                Aspect\SentryHttpClientFactoryAspect::class,
                Tracing\Aspect\CoroutineAspect::class,
                Tracing\Aspect\DbAspect::class,
                Tracing\Aspect\ElasticserachAspect::class,
                Tracing\Aspect\GuzzleHttpClientAspect::class,
                Tracing\Aspect\RpcAspect::class,
                Tracing\Aspect\RedisAspect::class,
                Tracing\Aspect\TraceAnnotationAspect::class,
            ],
            'commands' => [
                Command\AboutCommand::class,
                Command\TestCommand::class,
            ],
            'dependencies' => [
                \Sentry\ClientBuilderInterface::class => Factory\ClientBuilderFactory::class,
                \Sentry\State\HubInterface::class => Factory\HubFactory::class,
            ],
            'listeners' => [
                Listener\AmqpExceptionListener::class,
                Listener\AsyncQueueExceptionListener::class,
                Listener\CheckIsEnableRequestLifecycleListener::class,
                Listener\CommandExceptionListener::class,
                Listener\CrontabExceptionListener::class,
                Listener\DbQueryListener::class,
                Listener\KafkaExceptionListener::class,
                Listener\RequestExceptionListener::class,
                Listener\SetServerNameListener::class,
                Tracing\Listener\DbQueryListener::class,
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
