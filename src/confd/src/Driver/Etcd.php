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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Etcd\V3\KV;
use Psr\Container\ContainerInterface;

class Etcd implements DriverInterface
{
    private KV $client;

    private ?string $hashCache = null;

    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
        $this->client = make(KV::class, [
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
        $kvs = (array) ($this->client->fetchByPrefix($namespace)['kvs'] ?? []);

        return collect($kvs)
            ->filter(fn ($kv) => isset($mapping[$kv['key']]))
            ->mapWithKeys(fn ($kv) => [$mapping[$kv['key']] => $kv['value']])
            ->toArray();
    }

    public function isChanged(): bool
    {
        $namespace = (string) $this->config->get('confd.drivers.etcd.namespace', '');
        $watches = (array) $this->config->get('confd.drivers.etcd.watches', []);

        $kvs = (array) ($this->client->fetchByPrefix($namespace)['kvs'] ?? []);
        $values = collect($kvs)
            ->filter(fn ($kv) => in_array($kv['key'], $watches))
            ->mapWithKeys(fn ($kv) => [$kv['key'] => $kv['value']])
            ->toArray();

        $newHash = $this->getHash($values);
        $oldHash = $this->hashCache;

        return tap($oldHash && $oldHash != $newHash, function ($isChanged) use ($oldHash, $newHash) {
            if ($isChanged) {
                $this->logger->debug(sprintf('[confd#etcd] Config changed, pre_hash:%s cur_hash:%s.', $oldHash, $newHash));
            }

            // Upgrade new hash to cache
            $this->hashCache = $newHash;
        });
    }

    private function getHash($value): string
    {
        return md5(serialize($value));
    }
}
