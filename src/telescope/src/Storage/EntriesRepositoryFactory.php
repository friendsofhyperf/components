<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Storage;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class EntriesRepositoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $driver = $container->get(ConfigInterface::class)->get('telescope.driver', 'database');
        $manager = $container->get(EntriesRepositoryManager::class);

        return $manager->get($driver);
    }
}
