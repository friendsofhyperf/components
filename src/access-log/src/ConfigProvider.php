<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AccessLog;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'dependencies' => [
                Handler::class => HandlerFactory::class,
            ],
            'listeners' => [
                Listener\RequestTerminatedListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config of access_log.',
                    'source' => __DIR__ . '/../publish/access_log.php',
                    'destination' => BASE_PATH . '/config/autoload/access_log.php',
                ],
            ],
        ];
    }
}
