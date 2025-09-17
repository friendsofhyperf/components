<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Transport;

use Psr\Container\ContainerInterface;

class AsyncHttpTransportFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(\Hyperf\Contract\ConfigInterface::class);

        return new AsyncHttpTransport(
            $container->get(\Sentry\ClientBuilder::class),
            (int) $config->get('sentry.transport_channel_size', 65535),
            (int) $config->get('sentry.transport_concurrent_limit', 1000)
        );
    }
}
