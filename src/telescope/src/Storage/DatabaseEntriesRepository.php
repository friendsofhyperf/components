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
use Exception;
use FriendsOfHyperf\Telescope\Contract\ClearableRepository;
use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\Contract\PrunableRepository;
use FriendsOfHyperf\Telescope\Contract\TerminableRepository;
use FriendsOfHyperf\Telescope\EntryResult;
use FriendsOfHyperf\Telescope\EntryType;
use FriendsOfHyperf\Telescope\EntryUpdate;
use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Storage\Model\EntryModel;
use Hyperf\Collection\Collection;
use Hyperf\DbConnection\Db;
use Throwable;

use function Hyperf\Collection\collect;
use function Hyperf\Config\config;

class DatabaseEntriesRepository implements EntriesRepository, ClearableRepository, PrunableRepository, TerminableRepository
{
    /**
     * The tags currently being monitored.
     */
    protected ?array $monitoredTags = null;

    public function __construct(
        protected ?string $connection = null,
        protected ?int $chunkSize = null
    ) {
        $this->connection ??= (string) config('telescope.storage.database.connection', 'default');
        $this->chunkSize ??= (int) config('telescope.storage.dateabase.chunk', 200);
    }

    public function find($id): EntryResult
    {
        /** @var EntryModel $entry */
        $entry = EntryModel::on($this->connection)->where('uuid', $id)->firstOrFail();

        $tags = $this->table('telescope_entries_tags')
            ->where('entry_uuid', $id)
            ->pluck('tag')
            ->all();

        return new EntryResult(
            $entry->uuid,
            $entry->sequence,
            $entry->batch_id,
            $entry->type,
            $entry->family_hash,
            $entry->content,
            $entry->created_at,
            $tags
        );
    }

    public function get($type, EntryQueryOptions $options)
    {
        return EntryModel::on($this->connection) // @phpstan-ignore-line
            ->with('tags')
            ->withTelescopeOptions($type, $options)
            ->take($options->limit)
            ->orderByDesc('sequence')
            ->get()
            ->reject(fn ($entry) => ! is_array($entry->content))
            ->map(function ($entry) {
                return new EntryResult(
                    $entry->uuid,
                    $entry->sequence,
                    $entry->batch_id,
                    $entry->type,
                    $entry->family_hash,
                    $entry->content,
                    $entry->created_at,
                    $entry->tags->pluck('tag')->all(),
                );
            })
            ->values();
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

        /** @var Collection<int,IncomingEntry> $exceptions */
        /** @var Collection<int,IncomingEntry> $entries */
        [$exceptions, $entries] = $entries->partition->isException(); // @phpstan-ignore-line

        $this->storeExceptions($exceptions);

        $table = $this->table('telescope_entries');

        $entries->chunk($this->chunkSize)->each(function ($chunked) use ($table) {
            $table->insert($chunked->map(function ($entry) { // @phpstan-ignore-line
                $entry->content = json_encode($entry->content, JSON_INVALID_UTF8_SUBSTITUTE);

                return $entry->toArray();
            })->toArray());
        });

        $this->storeTags($entries->pluck('tags', 'uuid'));
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
            ->insert(
                collect($tags)->mapWithKeys(fn ($tag) => ['tag' => $tag])->all()
            );
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

    public function update($updates)
    {
        $failedUpdates = [];

        foreach ($updates as $update) {
            $entry = $this->table('telescope_entries')
                ->where('uuid', $update->uuid)
                ->where('type', $update->type)
                ->first();

            if (! $entry) {
                $failedUpdates[] = $update;

                continue;
            }

            $content = json_encode(array_merge(
                json_decode($entry->content ?? $entry['content'] ?? [], true) ?: [],
                $update->changes
            ));

            $this->table('telescope_entries')
                ->where('uuid', $update->uuid)
                ->where('type', $update->type)
                ->update(['content' => $content]);

            $this->updateTags($update);
        }

        return collect($failedUpdates);
    }

    /**
     * Update tags of the given entry.
     *
     * @param EntryUpdate $entry
     */
    protected function updateTags($entry)
    {
        if (! empty($entry->tagsChanges['added'])) {
            try {
                $this->table('telescope_entries_tags')->insert(
                    collect($entry->tagsChanges['added'])->map(function ($tag) use ($entry) {
                        return [
                            'entry_uuid' => $entry->uuid,
                            'tag' => $tag,
                        ];
                    })->toArray()
                );
            } catch (Exception $e) {
                // Ignore tags that already exist...
                if (! $this->isUniqueConstraintError($e)) {
                    throw $e;
                }
            }
        }

        collect($entry->tagsChanges['removed'])->each(function ($tag) use ($entry) {
            $this->table('telescope_entries_tags')->where([
                'entry_uuid' => $entry->uuid,
                'tag' => $tag,
            ])->delete();
        });
    }

    /**
     * Store the given array of exception entries.
     *
     * @param Collection|IncomingEntry[] $exceptions
     */
    protected function storeExceptions(Collection $exceptions)
    {
        $exceptions->chunk($this->chunkSize)->each(function ($chunked) {
            $this->table('telescope_entries')->insert($chunked->map(function ($exception) {
                $occurrences = $this->countExceptionOccurences($exception);

                $this->table('telescope_entries')
                    ->where('type', EntryType::EXCEPTION)
                    ->where('family_hash', $exception->familyHash())
                    ->update(['should_display_on_index' => false]);

                return array_merge($exception->toArray(), [
                    'family_hash' => $exception->familyHash(),
                    'content' => json_encode(array_merge(
                        $exception->content,
                        ['occurrences' => $occurrences + 1]
                    )),
                ]);
            })->toArray());
        });

        $this->storeTags($exceptions->pluck('tags', 'uuid'));
    }

    /**
     * Store the tags for the given entries.
     */
    protected function storeTags(Collection $results)
    {
        $results->chunk($this->chunkSize)->each(function ($chunked) {
            try {
                $this->table('telescope_entries_tags')
                    ->insert($chunked->flatMap(
                        fn ($tags, $uuid) => collect($tags)->map(fn ($tag) => ['entry_uuid' => $uuid, 'tag' => $tag])
                    )->all());
            } catch (Exception $e) {
                // Ignore tags that already exist...
                if (! $this->isUniqueConstraintError($e)) {
                    throw $e;
                }
            }
        });
    }

    /**
     * @internal
     * Determine if the given database exception was caused by a unique constraint violation
     */
    protected function isUniqueConstraintError(Exception $exception): bool
    {
        return boolval(preg_match('#Integrity constraint violation: 1062#i', $exception->getMessage()));
    }

    /**
     * Counts the occurences of an exception.
     *
     * @return int
     */
    protected function countExceptionOccurences(IncomingEntry $exception)
    {
        return $this->table('telescope_entries')
            ->where('type', EntryType::EXCEPTION)
            ->where('family_hash', $exception->familyHash())
            ->count();
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
