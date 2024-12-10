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

use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class EntriesRepositoryFactory
{
    public function __invoke(?ContainerInterface $container = null)
    {
        $config = $container->get(ConfigInterface::class);

        // Compatibility with v3.1
        $this->compatibility($config);

        $driver = $config->get('telescope.driver', 'database');

        if (! $config->has('telescope.storage.' . $driver)) {
            throw new InvalidArgumentException(sprintf('The driver [%s] has not been registered.', $driver));
        }

        $options = (array) $config->get('telescope.storage.' . $driver);

        if (! isset($options['driver'])) {
            throw new InvalidArgumentException(sprintf('The driver [%s] has not been registered.', $driver));
        }

        $driver = make($options['driver']);

        if (is_callable($driver)) {
            $driver = $driver($container, $options);
        }

        if ($driver instanceof EntriesRepository) {
            return $driver;
        }

        throw new InvalidArgumentException(sprintf('The driver [%s] must be an instance of %s.', $driver, EntriesRepository::class));
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2
     */
    private function compatibility(ConfigInterface $config)
    {
        if (
            ! $config->has('telescope.storage')
            && $config->has('telescope.database')
        ) {
            $config->set('telescope.storage.database', $config->get('telescope.database'));
        }
        if (
            $config->has('telescope.storage.database')
            && ! $config->has('telescope.storage.database.driver')
        ) {
            $config->set('telescope.storage.database.driver', DatabaseEntriesRepository::class);
        }
    }
}
