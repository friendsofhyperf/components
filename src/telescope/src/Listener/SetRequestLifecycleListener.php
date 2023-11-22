<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\SwitchManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Server as HttpServer;
use Hyperf\RpcServer\Server as RpcServer;
use Hyperf\Server\Event;

class SetRequestLifecycleListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected SwitchManager $switchManager,
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $this->switchManager->isEnable('request')) {
            return;
        }

        $servers = $this->config->get('server.servers', []);

        foreach ($servers as &$server) {
            $callbacks = $server['callbacks'] ?? [];
            $handler = $callbacks[Event::ON_REQUEST][0] ?? $callbacks[Event::ON_RECEIVE][0] ?? null;

            if (! $handler) {
                continue;
            }

            if (is_a($handler, HttpServer::class, true) || is_a($handler, RpcServer::class, true)) {
                $server['options'] ??= [];
                $server['options']['enable_request_lifecycle'] = true;
            }
        }

        $this->config->set('server.servers', $servers);
    }
}
