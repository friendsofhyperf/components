<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AccessLog;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Handler::class => HandlerFactory::class,
            ],
            'listeners' => [
                Listener\RequestTerminatedListener::class,
                Listener\SetRequestLifecycleListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file of access-log.',
                    'source' => __DIR__ . '/../publish/access_log.php',
                    'destination' => BASE_PATH . '/config/autoload/access_log.php',
                ],
            ],
        ];
    }
}
