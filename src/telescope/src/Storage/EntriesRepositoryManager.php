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
use InvalidArgumentException;

class EntriesRepositoryManager
{
    /**
     * @var array<string,EntriesRepository>
     */
    protected array $regisitries = [];

    public function get(string $driver): EntriesRepository
    {
        if (! $this->has($driver)) {
            throw new InvalidArgumentException(sprintf('The driver [%s] has not been registered.', $driver));
        }

        return $this->regisitries[$driver];
    }

    public function has(string $driver): bool
    {
        return isset($this->regisitries[$driver]);
    }

    public function register(string $driver, EntriesRepository $repository): void
    {
        $this->regisitries[$driver] = $repository;
    }
}
