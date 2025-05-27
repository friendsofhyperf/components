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

use FriendsOfHyperf\Cache\Contract\Factory;
use FriendsOfHyperf\Cache\Contract\Repository;
use FriendsOfHyperf\Cache\Repository as CacheRepository;
use Hyperf\Cache\CacheManager as HyperfCacheManager;

use function Hyperf\Support\make;

class CacheManager implements Factory
{
    /**
     * @var Repository[]
     */
    protected array $drivers = [];

    public function __construct(protected HyperfCacheManager $cacheManager)
    {
    }

    /**
     * Get a cache driver instance.
     */
    public function store(string $name = 'default'): Repository
    {
        return $this->drivers[$name] ?? $this->drivers[$name] = $this->resolve($name);
    }

    /**
     * Alias for the "store" method.
     */
    public function driver(string $name = 'default'): Repository
    {
        return $this->store($name);
    }

    /**
     * Resolve a cache repository instance.
     */
    public function resolve(string $name): Repository
    {
        return make(CacheRepository::class, [
            'name' => $name,
            'driver' => $this->cacheManager->getDriver($name),
        ]);
    }
}
