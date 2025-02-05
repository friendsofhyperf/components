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
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class EntriesRepositoryFactory
{
    public function __invoke(?ContainerInterface $container = null)
    {
        $telescopeConfig = $container->get(TelescopeConfig::class);

        $options = array_replace(
            [
                'driver' => NullEntriesRepository::class,
            ],
            $telescopeConfig->getStorageOptions($telescopeConfig->getStorageDriver())
        );

        /** @var mixed $driver */
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
}
