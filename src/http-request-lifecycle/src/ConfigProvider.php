<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/http-request-lifecycle.
 *
 * @link     https://github.com/friendsofhyperf/http-request-lifecycle
 * @document https://github.com/friendsofhyperf/http-request-lifecycle/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\HttpRequestLifeCycle;

class ConfigProvider
{
    public function __invoke(): array
    {
        defined('BASE_PATH') or define('BASE_PATH', '');

        return [
            'dependencies' => [
                \Hyperf\HttpServer\Server::class => Server::class,
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
            'publish' => [],
        ];
    }
}
