<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache;

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

    public function store(string $name = 'default'): CacheInterface
    {
        return $this->drivers[$name] ?? $this->drivers[$name] = make(Cache::class, [
            'driver' => $this->cacheManager->getDriver($name),
        ]);
    }

    /**
     * @deprecated since 3.1, use `FriendsOfHyperf\Cache\CacheManager::store()` instead.
     */
    public function get(string $name = 'default'): CacheInterface
    {
        return $this->store($name);
    }
}
