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
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class EntriesRepositoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $driver = $config->get('telescope.driver', 'database');
        $connection = (string) $config->get('telescope.storage.database.connection', 'default');
        $chunkSize = (int) $config->get('telescope.storage.database.chunk', 1000);

        return match ($driver) {
            'database' => new DatabaseEntriesRepository($connection, $chunkSize),
            default => throw new InvalidArgumentException('Unsupported driver: ' . $driver),
        };
    }
}
