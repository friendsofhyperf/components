<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Contract;

use FriendsOfHyperf\Telescope\EntryResult;
use FriendsOfHyperf\Telescope\EntryUpdate;
use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;
use Hyperf\Collection\Collection;

interface EntriesRepository
{
    /**
     * @param Collection<int,IncomingEntry> $entries
     */
    public function store($entries): void;

    /**
     * Store the given entry updates and return the failed updates.
     * @param Collection<int,EntryUpdate> $updates
     */
    public function update($updates);

    /**
     * @return Collection<int,EntryResult>
     */
    public function get(?string $type, EntryQueryOptions $options);

    public function find($id): EntryResult;

    /**
     * Load the monitored tags from storage.
     */
    public function loadMonitoredTags(): void;

    /**
     * Determine if any of the given tags are currently being monitored.
     */
    public function isMonitoring(array $tags): bool;

    /**
     * Get the list of tags currently being monitored.
     */
    public function monitoring(): array;

    /**
     * Begin monitoring the given list of tags.
     */
    public function monitor(array $tags): void;

    /**
     * Stop monitoring the given list of tags.
     */
    public function stopMonitoring(array $tags): void;
}
