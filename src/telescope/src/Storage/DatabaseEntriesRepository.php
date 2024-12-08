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
use FriendsOfHyperf\Telescope\Model\EntryModel;
use FriendsOfHyperf\Telescope\Model\EntryTagModel;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use Hyperf\DbConnection\Db;
use Throwable;

use function Hyperf\Collection\collect;

class DatabaseEntriesRepository implements EntriesRepository, ClearableRepository, PrunableRepository, TerminableRepository
{
    /**
     * The tags currently being monitored.
     */
    protected ?array $monitoredTags = null;

    protected string $connection;

    protected int $chunkSize;

    public function __construct(protected TelescopeConfig $telescopeConfig)
    {
        $this->connection = $telescopeConfig->getDatabaseConnection();
        $this->chunkSize = $telescopeConfig->getDatabaseChunk();
    }

    public function find($id): EntryResult
    {
        /** @var EntryModel $entry */
        $entry = EntryModel::on($this->connection)->where('uuid', $id)->firstOrFail();

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
        return EntryModel::on($this->connection) // @phpstan-ignore-line
            ->withTelescopeOptions($type, $options)
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

    public function prune(DateTimeInterface $before, bool $keepExceptions): int
    {
        $totalDeleted = 0;
        do {
            $deleted = $this->table('telescope_entries')
                ->where('created_at', '<', $before)
                ->when($keepExceptions, fn ($query) => $query->where('type', '!=', 'exception'))
                ->take($this->chunkSize)
                ->delete();
            $totalDeleted += $deleted;
        } while ($deleted !== 0);

        do {
            $deleted = $this->table('telescope_monitoring')
                ->take($this->chunkSize)
                ->delete();
        } while ($deleted !== 0);

        return $totalDeleted;
    }

    public function store($entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        $entries->each(function ($entry) {
            EntryModel::on($this->connection)->create($entry->toArray());
            foreach ($entry->tags as $tag) {
                EntryTagModel::on($this->connection)->create([
                    'entry_uuid' => $entry->uuid,
                    'tag' => $tag,
                ]);
            }
        });
    }

    /**
     * Load the monitored tags from storage.
     */
    public function loadMonitoredTags(): void
    {
        try {
            $this->monitoredTags = $this->monitoring();
        } catch (Throwable $e) {
            $this->monitoredTags = [];
        }
    }

    /**
     * Determine if any of the given tags are currently being monitored.
     */
    public function isMonitoring(array $tags): bool
    {
        if (is_null($this->monitoredTags)) {
            $this->loadMonitoredTags();
        }

        return count(array_intersect($tags, $this->monitoredTags)) > 0;
    }

    /**
     * Get the list of tags currently being monitored.
     */
    public function monitoring(): array
    {
        return $this->table('telescope_monitoring')->pluck('tag')->all();
    }

    /**
     * Begin monitoring the given list of tags.
     */
    public function monitor(array $tags): void
    {
        $tags = array_diff($tags, $this->monitoring());

        if (empty($tags)) {
            return;
        }

        $this->table('telescope_monitoring')
            ->insert(collect($tags)
                ->mapWithKeys(function ($tag) {
                    return ['tag' => $tag];
                })->all());
    }

    /**
     * Stop monitoring the given list of tags.
     */
    public function stopMonitoring(array $tags): void
    {
        $this->table('telescope_monitoring')->whereIn('tag', $tags)->delete();
    }

    /**
     * Perform any clean-up tasks needed after storing Telescope entries.
     */
    public function terminate(): void
    {
        $this->monitoredTags = null;
    }

    public function clear(): void
    {
        do {
            $deleted = $this->table('telescope_entries')->take($this->chunkSize)->delete();
        } while ($deleted !== 0);
        do {
            $deleted = $this->table('telescope_entries_tags')->take($this->chunkSize)->delete();
        } while ($deleted !== 0);
        do {
            $deleted = $this->table('telescope_monitoring')->take($this->chunkSize)->delete();
        } while ($deleted !== 0);
    }

    /**
     * Get a query builder instance for the given table.
     *
     * @return \Hyperf\Database\Query\Builder
     */
    protected function table(string $table)
    {
        return Db::connection($this->connection)->table($table);
    }
}
