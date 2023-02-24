<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\RequestLifeCycle;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Hyperf\HttpServer\Server::class => HttpServer::class,
                \Hyperf\GrpcServer\Server::class => GrpcServer::class,
            ],
        ];
    }
}
