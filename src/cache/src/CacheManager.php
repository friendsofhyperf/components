<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache;

use Hyperf\Cache\CacheManager as HyperfCacheManager;

class CacheManager
{
    /**
     * @var CacheInterface[]
     */
    protected array $drivers = [];

    public function __construct(protected HyperfCacheManager $cacheManager)
    {
    }

    public function get(string $name): CacheInterface
    {
        return $this->drivers[$name] ?? $this->drivers[$name] = make(Cache::class, [
            'driver' => $this->cacheManager->getDriver($name),
        ]);
    }
}
