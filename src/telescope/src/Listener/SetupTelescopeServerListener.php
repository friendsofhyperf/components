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

use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

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

        $servers = $this->config->get('server.servers');
        $servers[] = $this->telescopeConfig->getServerOptions();

        $this->config->set('server.servers', $servers);
    }
}
