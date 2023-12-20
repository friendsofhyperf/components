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

use Hyperf\Codec\Json;
use Hyperf\Codec\Xml;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use InvalidArgumentException;
use Override;
use Psr\Container\ContainerInterface;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class Nacos implements DriverInterface
{
    private Application $client;

    public function __construct(private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
        $config = $this->config->get('confd.drivers.nacos.client') ?: $this->config->get('nacos', []);

        if (empty($config)) {
            throw new InvalidArgumentException('Nacos config is invalid.');
        }

        $this->client = make(NacosClient::class, [
            'config' => $this->buildNacosConfig($config),
        ]);
    }

    /**
     * get all listener config from nacos server.
     */
    #[Override]
    public function fetch(): array
    {
        $listeners = $this->config->get('confd.drivers.nacos.listener_config', []);
        $mapping = (array) $this->config->get('confd.drivers.nacos.mapping', []);

        $values = collect($listeners)
            ->map(fn ($options) => $this->pull($options))
            ->toArray();

        return collect($mapping)
            ->filter(fn ($envKey, $configKey) => Arr::has($values, $configKey))
            ->mapWithKeys(fn ($envKey, $configKey) => [$envKey => Arr::get($values, $configKey)])
            ->toArray();
    }

    /**
     * get nacos client config from confd setting.
     */
    protected function buildNacosConfig(array $config): Config
    {
        if (! empty($config['uri'])) {
            $baseUri = $config['uri'];
        } else {
            $baseUri = sprintf('http://%s:%d', $config['host'] ?? '127.0.0.1', $config['port'] ?? 8848);
        }

        return new Config([
            'base_uri' => $baseUri,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'guzzle_config' => $config['guzzle']['config'] ?? null,
        ]);
    }

    /**
     * pull fresh config from nacos server.
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Hyperf\Codec\Exception\InvalidArgumentException
     */
    protected function pull(array $options = []): array|string
    {
        $dataId = $options['data_id'];
        $group = $options['group'];
        $tenant = $options['tenant'] ?? null;
        $type = $options['type'] ?? null;

        $response = $this->client->config->get($dataId, $group, $tenant);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(sprintf('The config of %s.%s.%s read failed from Nacos.', $group, $tenant, $dataId));
            return [];
        }

        return $this->decode((string) $response->getBody(), $type);
    }

    protected function decode(string $body, ?string $type = null): mixed
    {
        return match (strtolower((string) $type)) {
            'json' => Json::decode($body),
            'yml', 'yaml' => yaml_parse($body),
            'xml' => Xml::toArray($body),
            default => $body
        };
    }
}
