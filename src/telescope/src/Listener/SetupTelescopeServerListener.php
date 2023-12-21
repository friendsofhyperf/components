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

use FriendsOfHyperf\Telescope\Server\Server;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\Server\Event;
use Hyperf\Server\ServerInterface;

class SetupTelescopeServerListener implements ListenerInterface
{
    public function __construct(
        private ConfigInterface $config,
        private TelescopeConfig $telescopeConfig,
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
        if (! $this->telescopeConfig->isServerEnable()) {
            return;
        }

        $host = $this->telescopeConfig->getServerHost();
        $port = $this->telescopeConfig->getServerPort();

        $servers = $this->config->get('server.servers');

        $servers[] = [
            'name' => 'telescope',
            'type' => ServerInterface::SERVER_HTTP,
            'host' => $host,
            'port' => $port,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_REQUEST => [Server::class, 'onRequest'],
            ],
        ];

        $this->config->set('server.servers', $servers);
    }
}
