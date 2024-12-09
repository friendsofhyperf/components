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

use DateTimeInterface;
use FriendsOfHyperf\Telescope\Contract\ClearableRepository;
use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\Contract\PrunableRepository;
use FriendsOfHyperf\Telescope\Contract\TerminableRepository;
use FriendsOfHyperf\Telescope\EntryResult;
use Hyperf\Contract\ConfigInterface;

class EntriesRepositoryProxy implements EntriesRepository, ClearableRepository, PrunableRepository, TerminableRepository
{
    /**
     * @var EntriesRepository|ClearableRepository|PrunableRepository|TerminableRepository
     */
    protected $storage;

    public function __construct(
        protected ConfigInterface $config,
        protected EntriesRepositoryManager $manager
    ) {
        $this->storage = $this->manager->get(
            $this->config->get('telescope.driver', 'database')
        );
    }

    public function store($entries): void
    {
        $this->storage->store($entries);
    }

    public function update($updates)
    {
        $this->storage->update($updates);
    }

    public function get(?string $type, EntryQueryOptions $options)
    {
        return $this->storage->get($type, $options);
    }

    public function find($id): EntryResult
    {
        return $this->storage->find($id);
    }

    public function loadMonitoredTags(): void
    {
        $this->storage->loadMonitoredTags();
    }

    public function isMonitoring(array $tags): bool
    {
        return $this->storage->isMonitoring($tags);
    }

    public function monitoring(): array
    {
        return $this->storage->monitoring();
    }

    public function monitor(array $tags): void
    {
        $this->storage->monitor($tags);
    }

    public function stopMonitoring(array $tags): void
    {
        $this->storage->stopMonitoring($tags);
    }

    public function clear(): void
    {
        $this->storage->clear();
    }

    public function prune(DateTimeInterface $before, bool $keepExceptions): int
    {
        return $this->storage->prune($before, $keepExceptions);
    }

    public function terminate(): void
    {
        $this->storage->terminate();
    }
}
