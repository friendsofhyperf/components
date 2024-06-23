<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use FriendsOfHyperf\Telescope\Contract\CacheInterface;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

class TelescopeFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function __invoke()
    {
        $cache = match (true) {
            $this->container->has(CacheInterface::class) => $this->container->get(CacheInterface::class),
            $this->container->has(PsrCacheInterface::class) => $this->container->get(PsrCacheInterface::class),
            default => null,
        };
        $config = $this->container->get(ConfigInterface::class);

        return new TelescopeConfig($config, $cache);
    }
}
