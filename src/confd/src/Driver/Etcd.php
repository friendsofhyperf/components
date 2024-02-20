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

use FriendsOfHyperf\Confd\Traits\Logger;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Etcd\V3\KV;
use Override;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class Etcd implements DriverInterface
{
    use Logger;

    private KV $client;

    private Timer $timer;

    public function __construct(private ConfigInterface $config)
    {
        $this->client = make(EtcdClient::class, [
            'uri' => (string) $this->config->get('confd.drivers.etcd.client.uri', ''),
            'version' => (string) $this->config->get('confd.drivers.etcd.client.version', 'v3beta'),
            'options' => [
                'timeout' => (int) $this->config->get('confd.drivers.etcd.client.timeout', 10),
            ],
        ]);

        $this->resolveLogger();
        $this->timer = new Timer($this->logger);
    }

    public function loop(callable $callback): void
    {
        $interval = (int) $this->config->get('confd.interval', 1);

        $this->timer->tick($interval, function () use ($callback) {
            $callback($this->fetch());
        });
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
