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
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Codec\Xml;
use Psr\Container\ContainerInterface;

class Nacos implements DriverInterface
{
    private Application $client;

    private array $origins = [];

    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
        $this->client = make(NacosClient::class, [
            'config' => $this->pendingNacosConfig(),
        ]);
    }

    /**
     * get all listener config from nacos server.
     */
    public function fetch(): array
    {
        $listener = $this->config->get('confd.drivers.nacos.listener_config', []);
        $mapping = (array) $this->config->get('confd.drivers.nacos.mapping', []);

        $config = [];
        foreach ($listener as $key => $item) {
            $config = collect($this->pullConfig($item))
                ->filter(fn ($item, $k) => isset($mapping[$key][$k]))
                ->mapWithKeys(fn ($item, $k) => [$mapping[$key][$k] => is_array($item) ? implode(',', $item) : $item])
                ->merge($config)
                ->toArray();
        }
        return $config;
    }

    /**
     * check watch config is chaged.
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Hyperf\Utils\Exception\InvalidArgumentException
     */
    public function getChanges(): array
    {
        $watches = (array) $this->config->get('confd.drivers.nacos.watches', []);

        $config = [];
        foreach ($watches as $item) {
            $configKey = sprintf('%s.%s.%s', $item['group'], $item['tenant'] ?? 'default', $item['data_id']);
            $config = collect($this->pullConfig($item))
                ->mapWithKeys(fn ($value) => [$configKey => is_array($value) ? implode(',', $value) : $value])
                ->merge($config)
                ->toArray();
        }

        if (! $this->origins) { // Return [] when first run.
            return tap([], fn () => $this->origins = $config);
        }

        $changes = array_diff_assoc($config, $this->origins);

        return tap($changes, function ($changes) use ($config) {
            if ($changes) {
                $this->logger->debug('[confd#nacos] Config changed.');
            }

            $this->origins = $config;
        });
    }

    protected function decode(string $body, ?string $type = null): mixed
    {
        $type = strtolower((string) $type);
        switch ($type) {
            case 'json':
                return Json::decode($body);
            case 'yml':
            case 'yaml':
                return yaml_parse($body);
            case 'xml':
                return Xml::toArray($body);
            default:
                return $body;
        }
    }

    /**
     * get nacos client config from confd setting.
     */
    protected function pendingNacosConfig(): Config
    {
        $clientConfig = $this->config->get('confd.drivers.nacos.client', []);

        if (! empty($clientConfig['uri'])) {
            $baseUri = $clientConfig['uri'];
        } else {
            $baseUri = sprintf('http://%s:%d', $clientConfig['host'] ?? '127.0.0.1', $clientConfig['port'] ?? 8848);
        }

        return new Config([
            'base_uri' => $baseUri,
            'username' => $clientConfig['username'] ?? null,
            'password' => $clientConfig['password'] ?? null,
            'guzzle_config' => $clientConfig['guzzle']['config'] ?? null,
        ]);
    }

    /**
     * pull fresh config from nacos server.
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Hyperf\Utils\Exception\InvalidArgumentException
     */
    protected function pullConfig(array $listenerConfig): array|string
    {
        $dataId = $listenerConfig['data_id'];
        $group = $listenerConfig['group'];
        $tenant = $listenerConfig['tenant'] ?? null;
        $type = $listenerConfig['type'] ?? null;
        $response = $this->client->config->get($dataId, $group, $tenant);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error(sprintf('The config of %s.%s.%s read failed from Nacos.', $group, $tenant, $dataId));
            return [];
        }

        return $this->decode((string) $response->getBody(), $type);
    }
}
