<?php

declare(strict_types=1);
/**
 * This file is part of cache.
 *
 * @link     https://github.com/friendsofhyperf/cache
 * @document https://github.com/friendsofhyperf/cache/blob/2.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Cache;

use Hyperf\Cache\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use TypeError;

class CacheFactory
{
    /**
     * @throws TypeError
     * @throws InvalidArgumentException
     * @return CacheInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->get('default');
    }
}
