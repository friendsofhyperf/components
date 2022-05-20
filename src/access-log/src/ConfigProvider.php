<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
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
            'aspects' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [],
            'listeners' => [],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'config file of package.',
                    'source' => __DIR__ . '/../publish/access_log.php',
                    'destination' => BASE_PATH . '/config/autoload/access_log.php',
                ],
            ],
        ];
    }
}
