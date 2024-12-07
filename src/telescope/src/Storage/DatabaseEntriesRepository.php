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
use FriendsOfHyperf\Telescope\EntryResult;
use FriendsOfHyperf\Telescope\Model\EntryModel;

class DatabaseEntriesRepository implements EntriesRepository
{
    public function store($entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        $entries->each(function ($entry) {
            $entry->store();
        });
    }

    public function get($type, EntryQueryOptions $options)
    {
        return EntryModel::withTelescopeOptions($type, $options) // @phpstan-ignore-line
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
}
