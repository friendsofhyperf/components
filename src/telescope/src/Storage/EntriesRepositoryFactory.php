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

use Closure;
use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class EntriesRepositoryFactory
{
    public function __invoke(?ContainerInterface $container = null)
    {
        $telescopeConfig = $container->get(TelescopeConfig::class);

        // Compatibility with v3.1
        $this->compatibilityWithLegacyConfig($container->get(ConfigInterface::class));

        $driver = $telescopeConfig->getStorageDriver();
        $options = $telescopeConfig->getStorageOptions($driver);

        if (! $options || ! isset($options['driver'])) {
            throw new InvalidArgumentException(sprintf('The driver [%s] has not been registered.', $driver));
        }

        $driver = $options['driver'];
        $instance = match (true) {
            $driver instanceof Closure => $driver($container, $options),
            is_string($driver) && class_exists($driver) && method_exists($driver, '__invoke') => make($driver, $options)($container, $options),
            is_a($driver, EntriesRepository::class, true) => is_string($driver) ? make($driver, $options) : $driver,
            default => null,
        };

        if ($instance instanceof EntriesRepository) {
            return $instance;
        }

        throw new InvalidArgumentException(sprintf('The driver [%s] must be an instance of %s.', $driver, EntriesRepository::class));
    }

    /**
     * @deprecated since v3.1, will be removed in v3.2
     */
    /**
     * 兼容旧配置，优化命名.
     */
    private function compatibilityWithLegacyConfig(ConfigInterface $config): void
    {
        if (! $config->has('telescope.storage') && $config->has('telescope.database')) {
            $config->set('telescope.storage.database', $config->get('telescope.database'));
        }
        if ($config->has('telescope.storage.database') && ! $config->has('telescope.storage.database.driver')) {
            $config->set('telescope.storage.database.driver', DatabaseEntriesRepository::class);
        }
    }
}
