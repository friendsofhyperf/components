<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ConfigConsul;

use Hyperf\Codec\Packer\JsonPacker;
use Hyperf\ConfigCenter\AbstractDriver;
use Hyperf\Contract\PackerInterface;
use Psr\Container\ContainerInterface;

class ConsulDriver extends AbstractDriver
{
    protected string $driverName = 'consul';

    protected PackerInterface $packer;

    protected array $mapping;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->client = $container->get(ClientInterface::class);
        $this->mapping = $this->config->get('config_center.drivers.consul.mapping', []);
        $this->packer = $container->get($this->config->get('config_center.drivers.consul.packer', JsonPacker::class));
    }

    protected function updateConfig(array $config): void
    {
        $configurations = $this->format($config);

        foreach ($configurations as $kv) {
            $key = $this->mapping[$kv->key] ?? null;

            if (is_string($key)) {
                $this->config->set($key, $this->packer->unpack((string) $kv->value));
                $this->logger->debug(sprintf('Config [%s] is updated', $key));
            }
        }
    }

    /**
     * Format kv configurations.
     * @return KV[]
     */
    protected function format(array $config): array
    {
        $result = [];

        foreach ($config as $value) {
            $result[] = new KV($value);
        }

        return $result;
    }
}
