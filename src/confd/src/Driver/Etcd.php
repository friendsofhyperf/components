<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd\Driver;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Etcd\V3\KV;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class Etcd implements DriverInterface
{
    private KV $client;

    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
        $this->client = make(EtcdClient::class, [
            'uri' => (string) $this->config->get('confd.drivers.etcd.client.uri', ''),
            'version' => (string) $this->config->get('confd.drivers.etcd.client.version', 'v3beta'),
            'options' => [
                'timeout' => (int) $this->config->get('confd.drivers.etcd.client.timeout', 10),
            ],
        ]);
    }

    public function fetch(): array
    {
        $namespace = (string) $this->config->get('confd.drivers.etcd.namespace', '');
        $mapping = (array) $this->config->get('confd.drivers.etcd.mapping', []);
        $kvs = collect((array) ($this->client->fetchByPrefix($namespace)['kvs'] ?? []))
            ->filter(fn ($kv) => isset($mapping[$kv['key']]))
            ->mapWithKeys(fn ($kv) => [$kv['key'] => $kv['value']])
            ->toArray();

        return collect($mapping)
            ->filter(fn ($envKey, $configKey) => Arr::has($kvs, $configKey))
            ->mapWithKeys(fn ($envKey, $configKey) => [$envKey => Arr::get($kvs, $configKey)])
            ->toArray();
    }
}
