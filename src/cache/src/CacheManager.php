<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Cache;

use FriendsOfHyperf\Cache\Contract\CacheInterface;
use Hyperf\Cache\CacheManager as HyperfCacheManager;

use function Hyperf\Support\make;

class CacheManager
{
    /**
     * @var CacheInterface[]
     */
    protected array $drivers = [];

    public function __construct(protected HyperfCacheManager $cacheManager)
    {
    }

    /**
     * Get a cache driver instance.
     */
    public function store(string $name = 'default'): CacheInterface
    {
        return $this->drivers[$name] ?? $this->drivers[$name] = $this->resolve($name);
    }

    /**
     * Alias for the "store" method.
     */
    public function driver(string $name = 'default'): CacheInterface
    {
        return $this->store($name);
    }

    /**
     * Resolve a cache repository instance.
     */
    public function resolve(string $name): CacheInterface
    {
        return make(Repository::class, [
            'name' => $name,
            'driver' => $this->cacheManager->getDriver($name),
        ]);
    }
}
