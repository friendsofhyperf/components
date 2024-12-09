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
    /**
     * @var array<string,EntriesRepository>
     */
    protected array $regisitries = [];

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config
    ) {
    }

    public function __invoke()
    {
        $driver = $this->config->get('telescope.driver', 'database');

        return $this->get($driver);
    }

    public function get(string $driver): EntriesRepository
    {
        if (! isset($this->regisitries[$driver])) {
            throw new InvalidArgumentException(sprintf('The driver [%s] has not been registered.', $driver));
        }

        return $this->regisitries[$driver];
    }

    public function register(): void
    {
        /** @var array<string, array{driver?: class-string<EntriesRepository>}> */
        $drivers = (array) $this->config->get('telescope.storage', []);

        foreach ($drivers as $driver => $options) {
            $driver = $options['driver'] ?? DatabaseEntriesRepository::class;
            $this->regisitries[$driver] = make($options['driver']);
        }
    }
}
