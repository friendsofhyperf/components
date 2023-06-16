<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConfigConsul\Consul;

use Hyperf\Consul\KV;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Psr\Container\ContainerInterface;

interface_exists(KVInterface::class); // !! Trigger autoload

class KVFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new KV(function () use ($container) {
            $config = $container->get(ConfigInterface::class);
            $token = $config->get('config_center.drivers.consul.token', '');
            $options = [
                'timeout' => 2,
                'base_uri' => $config->get('config_center.drivers.consul.uri', KV::DEFAULT_URI),
            ];

            if (! empty($token)) {
                $options['headers'] = [
                    'X-Consul-Token' => $token,
                ];
            }

            return $container->get(ClientFactory::class)->create($options);
        });
    }
}
