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
     * @return Collection<int,EntryResult>
     */
    public function get(?string $type, EntryQueryOptions $options);

    public function find($id): EntryResult;
}
