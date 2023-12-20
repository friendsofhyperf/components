<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd\Driver;

use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\V3\KV;
use Override;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class Etcd implements DriverInterface
{
    private KV $client;

    public function __construct(private ConfigInterface $config)
    {
        $this->client = make(EtcdClient::class, [
            'uri' => (string) $this->config->get('confd.drivers.etcd.client.uri', ''),
            'version' => (string) $this->config->get('confd.drivers.etcd.client.version', 'v3beta'),
            'options' => [
                'timeout' => (int) $this->config->get('confd.drivers.etcd.client.timeout', 10),
            ],
        ]);
    }

    #[Override]
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
