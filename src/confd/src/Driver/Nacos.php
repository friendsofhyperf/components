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

use Hyperf\ConfigNacos\Client;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Psr\Container\ContainerInterface;

class Nacos implements DriverInterface
{
    private Application $nacosClient;

    private Client $client;

    private array $origins = [];

    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
        $this->nacosClient = make(Application::class, ['config' => new Config($this->getNacosClientConfig())]);
        $this->client = $container->get(Client::class);
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

    /**
     * pull fresh config from nacos server.
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Hyperf\Utils\Exception\InvalidArgumentException
     */
    private function pullConfig(array $listenerConfig): array|string
    {
        $dataId = $listenerConfig['data_id'];
        $group = $listenerConfig['group'];
        $tenant = $listenerConfig['tenant'] ?? null;
        $type = $listenerConfig['type'] ?? null;

        $response = $this->nacosClient->config->get($dataId, $group, $tenant);
        if ($response->getStatusCode() !== 200) {
            $this->logger->error(sprintf('The config of %s.%s.%s read failed from Nacos.', $group, $tenant, $dataId));
            return [];
        }

        return $this->client->decode((string) $response->getBody(), $type);
    }

    /**
     * get nacos client config from confd setting.
     */
    private function getNacosClientConfig(): array
    {
        $clientConfig = $this->config->get('confd.drivers.nacos.client', []);

        if (! empty($clientConfig['uri'])) {
            $baseUri = $clientConfig['uri'];
        } else {
            $baseUri = sprintf('http://%s:%d', $clientConfig['host'] ?? '127.0.0.1', $clientConfig['port'] ?? 8848);
        }

        return [
            'base_uri' => $baseUri,
            'username' => $clientConfig['username'] ?? null,
            'password' => $clientConfig['password'] ?? null,
            'guzzle_config' => $clientConfig['guzzle']['config'] ?? null,
        ];
    }
}
