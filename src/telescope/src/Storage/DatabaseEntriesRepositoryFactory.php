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

class DatabaseEntriesRepositoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $connection = $config->get('telescope.database.connection', 'default');
        $chunkSize = $config->get('telescope.database.chunk');

        return new DatabaseEntriesRepository($connection, $chunkSize);
    }
}
