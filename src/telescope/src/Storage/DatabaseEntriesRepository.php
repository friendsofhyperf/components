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
use FriendsOfHyperf\Telescope\EntryResult;
use FriendsOfHyperf\Telescope\Model\EntryModel;
use FriendsOfHyperf\Telescope\Model\EntryTagModel;
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\DbConnection\Db;

class DatabaseEntriesRepository implements EntriesRepository, ClearableRepository, PrunableRepository
{
    public function clear(): void
    {
        $connection = Telescope::getConfig()->getDatabaseConnection();
        Db::connection($connection)->table('telescope_entries')->delete();
        Db::connection($connection)->table('telescope_entries_tags')->delete();
        Db::connection($connection)->table('telescope_monitoring')->delete();
    }

    public function find($id): EntryResult
    {
        /** @var EntryModel $entry */
        $entry = EntryModel::query()->findOrFail($id);

        return new EntryResult(
            $entry->uuid,
            $entry->sequence,
            $entry->batch_id,
            $entry->type,
            $entry->family_hash,
            $entry->content,
            $entry->created_at,
            $entry->tags
        );
    }

    public function get($type, EntryQueryOptions $options)
    {
        return EntryModel::withTelescopeOptions($type, $options)
            ->take($options->limit)
            ->orderByDesc('sequence')
            ->get()->reject(function ($entry) {
                return ! is_array($entry->content);
            })->map(function ($entry) {
                return new EntryResult(
                    $entry->uuid,
                    $entry->sequence,
                    $entry->batch_id,
                    $entry->type,
                    $entry->family_hash,
                    $entry->content,
                    $entry->created_at,
                    []
                );
            })->values();
    }

    public function prune(DateTimeInterface $before, $keepExceptions): int
    {
        $connection = Telescope::getConfig()->getDatabaseConnection();
        $deleted = Db::connection($connection)->table('telescope_entries')
            ->where('created_at', '<', $before)
            ->when($keepExceptions, fn ($query) => $query->where('type', '!=', 'exception'))
            ->delete();
        Db::connection($connection)
            ->table('telescope_monitoring')
            ->delete();

        return $deleted;
    }

    public function store($entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        $entries->each(function ($entry) {
            EntryModel::query()->create($entry->toArray());
            foreach ($entry->tags as $tag) {
                EntryTagModel::query()->create([
                    'entry_uuid' => $entry->uuid,
                    'tag' => $tag,
                ]);
            }
        });
    }
}
