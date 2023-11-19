<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AccessLog\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Server;
use Hyperf\Server\Event;

class SetRequestLifecycleListener implements ListenerInterface
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $servers = $this->config->get('server.servers', []);

        foreach ($servers as &$server) {
            $callbacks = $server['callbacks'] ?? [];
            $onRequestHandler = $callbacks[Event::ON_REQUEST][0] ?? null;

            if (! $onRequestHandler) {
                continue;
            }

            if ($onRequestHandler == Server::class || is_a($onRequestHandler, Server::class)) {
                $server['options'] ??= [];
                $server['options']['enable_request_lifecycle'] = true;
            }
        }

        $this->config->set('server.servers', $servers);
    }
}
