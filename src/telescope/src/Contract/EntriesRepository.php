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
use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;
use FriendsOfHyperf\Telescope\Storage\IncomingEntry;
use Hyperf\Collection\Collection;

interface EntriesRepository
{
    /**
     * Return an entry with the given ID.
     *
     * @param mixed $id
     */
    public function find($id): EntryResult;

    /**
     * Return all the entries of a given type.
     *
     * @param string|null $type
     * @return Collection|EntryResult[]
     */
    public function get($type, EntryQueryOptions $options);

    /**
     * Store the given entries.
     *
     * @param Collection|IncomingEntry[] $entries
     */
    public function store(Collection $entries): void;

    /**
     * Store the given entry updates and return the failed updates.
     *
     * @param Collection|EntryUpdate[] $updates
     * @return Collection|null
     */
    public function update(Collection $updates);

    /**
     * Load the monitored tags from storage.
     */
    public function loadMonitoredTags();

    /**
     * Determine if any of the given tags are currently being monitored.
     *
     * @return bool
     */
    public function isMonitoring(array $tags);

    /**
     * Get the list of tags currently being monitored.
     *
     * @return array
     */
    public function monitoring();

    /**
     * Begin monitoring the given list of tags.
     */
    public function monitor(array $tags);

    /**
     * Stop monitoring the given list of tags.
     */
    public function stopMonitoring(array $tags);
}
