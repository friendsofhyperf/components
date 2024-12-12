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

use Carbon\Carbon;
use DateTimeInterface;
use FriendsOfHyperf\Telescope\Contract\ClearableRepository;
use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\Contract\PrunableRepository;
use FriendsOfHyperf\Telescope\Contract\TerminableRepository;
use FriendsOfHyperf\Telescope\EntryResult;

use function Hyperf\Collection\collect;

class NullEntriesRepository implements EntriesRepository, ClearableRepository, PrunableRepository, TerminableRepository
{
    public function store($entries): void
    {
    }

    public function update($updates)
    {
    }

    public function get(?string $type, EntryQueryOptions $options)
    {
        return collect();
    }

    public function find($id): EntryResult
    {
        return new EntryResult(
            id: null,
            sequence: null,
            batchId: '',
            type: '',
            familyHash: '',
            content: [],
            createdAt: Carbon::now(),
        );
    }

    public function loadMonitoredTags(): void
    {
    }

    public function isMonitoring(array $tags): bool
    {
        return false;
    }

    public function monitoring(): array
    {
        return [];
    }

    public function monitor(array $tags): void
    {
    }

    public function stopMonitoring(array $tags): void
    {
    }

    public function clear(): void
    {
    }

    public function prune(DateTimeInterface $before, bool $keepExceptions): int
    {
        return 0;
    }

    public function terminate(): void
    {
    }
}
