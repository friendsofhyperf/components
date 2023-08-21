<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Factory;

use Psr\Container\ContainerInterface;
use Sentry\ClientBuilderInterface;
use Sentry\State\Hub;

use function Hyperf\Support\make;

/**
 * @property \Sentry\Transport\TransportInterface|null $transport
 * @method \Sentry\ClientInterface getClient()
 */
class HubFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $clientBuilder = $container->get(ClientBuilderInterface::class);
        $client = (function () {
            $this->transport = null; // Make the transport is new created before get client
            return $this->getClient();
        })->call($clientBuilder);

        return new Hub($client);
    }
}
