<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache;

use Hyperf\Cache\CacheManager as HyperfCacheManager;
use Psr\Container\ContainerInterface;

class CacheManager
{
    /**
     * @var CacheInterface[]
     */
    protected $drivers = [];

    /**
     * @var HyperfCacheManager
     */
    protected $cacheManager;

    public function __construct(ContainerInterface $container)
    {
        $this->cacheManager = $container->get(HyperfCacheManager::class);
    }

    public function get(string $name): CacheInterface
    {
        if (! isset($this->drivers[$name]) || ! $this->drivers[$name] instanceof CacheInterface) {
            $this->drivers[$name] = make(Cache::class, [
                'driver' => $this->cacheManager->getDriver($name),
            ]);
        }

        return $this->drivers[$name];
    }
}
