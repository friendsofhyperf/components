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

use Hyperf\Cache\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use TypeError;

class CacheFactory
{
    /**
     * @return CacheInterface
     * @throws TypeError
     * @throws InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->store('default');
    }
}
