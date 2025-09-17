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
        $clientBuilder = $container->get(\Sentry\ClientBuilder::class);
        $channelSize = $config->get('sentry.transport_channel_size', 65535);

        return new AsyncHttpTransport($clientBuilder, $channelSize);
    }
}
