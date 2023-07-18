<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConfigConsul\Consul;

use Hyperf\Consul\KV;
use Hyperf\Consul\KVInterface as KVContract;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Guzzle\ClientFactory;
use Psr\Container\ContainerInterface;

interface_exists(KVInterface::class); // !! Trigger autoload

class KVFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if (! $config = $container->get(ConfigInterface::class)->get('config_center.drivers.consul.client')) {
            return $container->get(KVContract::class);
        }

        $token = $config['token'] ?? '';
        $uri = $config['uri'] ?? KV::DEFAULT_URI;

        return new KV(function () use ($container, $token, $uri) {
            $options = [
                'timeout' => 2,
                'base_uri' => $uri,
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
