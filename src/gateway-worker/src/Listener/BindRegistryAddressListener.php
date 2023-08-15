<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\GatewayWorker\Listener;

use FriendsOfHyperf\GatewayWorker\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class BindRegistryAddressListener implements ListenerInterface
{
    public function __construct(protected ConfigInterface $config, protected StdoutLoggerInterface $logger)
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
        if ($registryAddress = $this->config->get('gatewayworker.register_address', '')) {
            Client::$registerAddress = $registryAddress;

            $this->logger->debug(sprintf('[gateway-worker] Bind registry address %s', $registryAddress));
        }
    }
}
