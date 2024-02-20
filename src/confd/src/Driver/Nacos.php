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
use Hyperf\Codec\Json;
use Hyperf\Codec\Xml;
use Hyperf\Collection\Arr;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coordinator\Timer;
use Hyperf\Nacos\Application;
use Hyperf\Nacos\Config;
use Hyperf\Nacos\Module;
use Hyperf\Nacos\Protobuf\ListenHandler\ConfigChangeNotifyRequestHandler;
use Hyperf\Nacos\Protobuf\Response\ConfigQueryResponse;
use InvalidArgumentException;
use Override;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;

class Nacos implements DriverInterface
{
    use Logger;

    private Application $client;

    private Timer $timer;

    public function __construct(private ConfigInterface $config)
    {
        $config = $this->config->get('confd.drivers.nacos.client') ?: $this->config->get('nacos', []);

        if (empty($config)) {
            throw new InvalidArgumentException('Nacos config is invalid.');
        }

        $this->client = make(NacosClient::class, [
            'config' => $this->buildNacosConfig($config),
        ]);

        $this->resolveLogger();
        $this->timer = new Timer($this->logger);
    }

    public function loop(callable $callback): void
    {
        $isGrpcEnabled = $this->config->get('confd.drivers.nacos.client.grpc.enable', false);

        if ($isGrpcEnabled) {
            foreach ($this->config->get('confd.drivers.nacos.listener_config', []) as $options) {
                $dataId = $options['data_id'];
                $group = $options['group'];
                $tenant = $options['tenant'] ?? null;
                $type = $options['type'] ?? null;
                $client = $this->client->grpc->get($tenant, Module::CONFIG);
                $client->listenConfig($group, $dataId, new ConfigChangeNotifyRequestHandler(function (ConfigQueryResponse $response) use ($callback, $dataId, $type) {
                    $config = $response->getContent();
                    $values = $this->mapping([
                        $dataId => $this->decode($config, $type),
                    ]);
                    $callback($values);
                }));
            }
            foreach ($this->client->grpc->moduleClients(Module::CONFIG) as $client) {
                $client->listen();
            }
            return;
        }

        $interval = (int) $this->config->get('confd.interval', 1);

        $this->timer->tick($interval, function () use ($callback) {
            $callback($this->fetch());
        });
    }

    /**
     * get all listener config from nacos server.
     */
    #[Override]
    public function fetch(): array
    {
        $listeners = (array) $this->config->get('confd.drivers.nacos.listener_config', []);
        $values = collect($listeners)
            ->map(fn ($options) => $this->pull($options))
            ->toArray();

        return $this->mapping($values);
    }

    protected function mapping(array $values): array
    {
        $mapping = (array) $this->config->get('confd.drivers.nacos.mapping', []);

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
            'access_key' => $config['access_key'] ?? null,
            'access_secret' => $config['access_secret'] ?? null,
            'guzzle_config' => $config['guzzle']['config'] ?? null,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'grpc' => $config['grpc'] ?? [],
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
