<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TelescopeElasticsearch\Storage;

use Carbon\Carbon;
use DateTimeInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;
use FriendsOfHyperf\Telescope\Contract\ClearableRepository;
use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\Contract\PrunableRepository;
use FriendsOfHyperf\Telescope\Contract\TerminableRepository;
use FriendsOfHyperf\Telescope\EntryResult;
use FriendsOfHyperf\Telescope\EntryType;
use FriendsOfHyperf\Telescope\EntryUpdate;
use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Str;
use Throwable;

use function Hyperf\Collection\collect;
use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

/**
 * Class ElasticsearchEntriesRepository.
 */
class ElasticsearchEntriesRepository implements EntriesRepository, ClearableRepository, PrunableRepository, TerminableRepository
{
    /**
     * The tags currently being monitored.
     */
    protected ?array $monitoredTags = null;

    private EntriesIndex $index;

    public function __construct(string $index = 'telescope_entries', array $options = [])
    {
        $this->index = make(EntriesIndex::class, $options + ['index' => $index]);
    }

    /**
     * Return an entry with the given ID.
     *
     * @param mixed $id
     *
     * @throws Exception
     */
    public function find($id): EntryResult
    {
        $response = $this->index->client()->get([
            'index' => $this->index->index,
            'id' => $id,
        ]);

        if (! $response->asBool()) {
            throw new Exception('Entry not found');
        }

        $entry = collect($response->asArray());

        return $this->toEntryResult($entry->all());
    }

    /**
     * Return all the entries of a given type.
     *
     * @param string|null $type
     *
     * @return Collection<array-key,EntryResult>
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function get($type, EntryQueryOptions $options)
    {
        if ($options->limit < 0) {
            $options->limit = 1000;
        }
        $options->beforeSequence = $options->beforeSequence;
        $query = [
            'from' => (int) $options->beforeSequence,
            'size' => $options->limit,
            'sort' => [
                [
                    'created_at' => [
                        'order' => 'desc',
                    ],
                ],
            ],
            'query' => [
                'bool' => [
                    'must' => [],
                ],
            ],
        ];

        if ($type) {
            $query['query']['bool']['must'][] = [
                'term' => [
                    'type' => $type,
                ],
            ];
        }

        if ($options->batchId) {
            $query['query']['bool']['must'][] = [
                'term' => [
                    'batch_id' => $options->batchId,
                ],
            ];
        }

        if ($options->familyHash) {
            $query['query']['bool']['must'][] = [
                'term' => [
                    'family_hash' => $options->familyHash,
                ],
            ];
        }

        if ($options->tag) {
            $query['query']['bool']['must'][] = [
                'nested' => [
                    'path' => 'tags',
                    'query' => [
                        'match_phrase' => [
                            'tags.raw' => $options->tag,
                        ],
                    ],
                ],
            ];
        }
        $params = [
            'index' => $this->index->index,
            'type' => $type,
            'body' => [
                ...$query,
            ],
        ];
        $response = $this->index->client()->search($params);

        return $this->toEntryResults(collect($response->asArray()), $options)
            ->reject(fn ($entry) => ! is_array($entry->content));
    }

    /**
     * Map Elasticsearch result to EntryResult collection.
     */
    public function toEntryResults(Collection $results, EntryQueryOptions $options): Collection
    {
        $entries = collect($results->all()['hits']['hits']);

        return $entries->map(fn ($entry) => $this->toEntryResult($entry, $options));
    }

    /**
     * Map Elasticsearch document to EntryResult object.
     */
    public function toEntryResult(array $document, ?EntryQueryOptions $options = null): EntryResult
    {
        $entry = $document['_source'] ?? [];
        $requestSequence = 50;

        if ($options?->beforeSequence >= 50) {
            $requestSequence = $options->beforeSequence + $requestSequence;
        }

        return new EntryResult(
            $entry['uuid'],
            $requestSequence,
            $entry['batch_id'],
            $entry['type'],
            $entry['family_hash'] ?? null,
            $entry['content'],
            Carbon::parse($entry['created_at']),
            Arr::pluck($entry['tags'], 'raw')
        );
    }

    /**
     * Store the given entries.
     *
     * @param Collection<array-key,IncomingEntry> $entries
     *
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function store($entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        [$exceptions, $entries] = $entries->partition->isException(); // @phpstan-ignore-line

        $this->storeExceptions($exceptions);

        $this->bulkSend($entries);
    }

    /**
     * Map Elasticsearch result to IncomingEntry object.
     */
    public function toIncomingEntry(array $document): IncomingEntry
    {
        $data = $document['_source'] ?? [];

        return tap(IncomingEntry::make($data['content']), function ($entry) use ($data) {
            $entry->uuid = $data['uuid'];
            $entry->batchId = $data['batch_id'];
            $entry->type = $data['type'];
            $entry->familyHash = $data['family_hash'] ?? null;
            $entry->recordedAt = Carbon::parse($data['created_at']);
            $entry->tags = Arr::pluck($data['tags'], 'raw');

            if (! empty($data['content']['user'])) {
                $entry->user = $data['content']['user'];
            }
        });
    }

    /**
     * Store the given entry updates.
     *
     * @param Collection<array-key,EntryUpdate> $updates
     *
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function update($updates): void
    {
        $entries = [];
        foreach ($updates as $update) {
            $params = [
                'index' => $this->index->index, // Replace with your actual index name
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['uuid' => $update->uuid]],
                                ['term' => ['type' => $update->type]],
                            ],
                        ],
                    ],
                    'size' => 1,
                ],
            ];
            $entry = $this->index->client()->search($params)->asArray();

            if (
                ! isset($entry['hits']['hits'][0])
                || (gettype($entry['hits']['hits'][0]) !== 'array')
            ) {
                continue;
            }

            $collectEntries = collect($entry['hits']['hits'][0])->toArray();

            $collectEntries['_source']['content'] = array_merge(
                $collectEntries['_source']['content'] ?? [],
                $update->changes
            );

            $entries[] = tap($this->toIncomingEntry($collectEntries), function ($e) use ($update) {
                $e->tags($this->updateTags($update, $e->tags));
            });
        }

        $this->bulkSend(collect($entries));
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
     * Get the list of tags currently being monitored.
     */
    public function monitoring(): array
    {
        return [];
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
    }

    /**
     * Stop monitoring the given list of tags.
     */
    public function stopMonitoring(array $tags): void
    {
    }

    /**
     * Prune all of the entries older than the given date.
     *
     * @param bool $keepExceptions
     *
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function prune(DateTimeInterface $before, $keepExceptions): int
    {
        $params = [
            'index' => $this->index->index, // Replace with your actual index name
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'range' => [
                                    'created_at' => [
                                        'lt' => $before->format('Y-m-d H:i:s'),
                                    ],
                                ],
                            ],
                            [
                                'match_all' => (object) [],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->index->client()->deleteByQuery($params);

        return $response['total'] ?? 0;
    }

    /**
     * Clear all the entries.
     *
     * @throws ClientResponseException
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    public function clear(): void
    {
        $this->index->client()->indices()->delete(['index' => $this->index->index]);
    }

    /**
     * Perform any clean-up tasks needed after storing Telescope entries.
     */
    public function terminate(): void
    {
        $this->monitoredTags = null;
    }

    /**
     * Store the given array of exception entries.
     *
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    protected function storeExceptions(Collection $exceptions): void
    {
        $entries = collect([]);

        $exceptions->map(function ($exception) use ($entries) {
            $params = [
                'index' => $this->index->index, // Replace with your actual index name
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => [
                                ['term' => ['family_hash' => $exception->familyHash()]],
                                ['term' => ['type' => EntryType::EXCEPTION]],
                            ],
                        ],
                    ],
                    'size' => 1000,
                ],
            ];
            $documents = $this->index->client()->search($params);
            $content = array_merge(
                $exception->content,
                ['occurrences' => $documents->offsetGet('hits')['total']['value'] + 1]
            );
            $collectDocuments = collect($documents->asArray()['hits']['hits']);
            $entries->merge(
                $collectDocuments->map(function ($document) {
                    $document = collect($document)->toArray();

                    return tap($this->toIncomingEntry($document), function ($entry) {
                        $entry->displayOnIndex = false;
                    });
                })
            );

            $exception->content = $content;
            $exception->tags([
                get_class($exception->exception),
            ]);

            $entries->push($exception);

            $occurrences = $collectDocuments->map(function ($document) {
                $document = collect($document)->toArray();

                return tap($this->toIncomingEntry($document), function ($entry) {
                    $entry->displayOnIndex = false;
                });
            });

            $entries->merge($occurrences);
        });
        $this->bulkSend($entries);
    }

    /**
     * Use Elasticsearch bulk API to send list of documents.
     *
     * @param Collection<IncomingEntry> $entries
     *
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    protected function bulkSend(Collection $entries): void
    {
        if ($entries->isEmpty()) {
            return;
        }

        $this->initIndex($index = $this->index);

        $params['body'] = [];
        foreach ($entries as $entry) {
            $params['body'][] = [
                'index' => [
                    '_id' => $entry->uuid,
                    '_index' => $index->index,
                ],
            ];
            $data = $entry->toArray();
            $data['family_hash'] = $entry->familyHash ?: ($entry->familyHash() ?? null);
            $data['tags'] = $this->formatTags($entry->tags);
            $data['should_display_on_index'] = property_exists($entry, 'displayOnIndex')
                ? $entry->displayOnIndex
                : true;
            $data['@timestamp'] = gmdate('c');

            $params['body'][] = $data;
        }

        $index->client()->bulk($params);
    }

    /**
     * Create new index if not exists.
     */
    protected function initIndex(EntriesIndex $index): void
    {
        if (! $index->exists()) {
            $index->create();
        }
    }

    /**
     * Format tags to elasticsearch input.
     */
    protected function formatTags(array $tags): array
    {
        $formatted = [];

        foreach ($tags as $tag) {
            if (Str::contains($tag, ':')) {
                [$name, $value] = explode(':', $tag);
            } else {
                $name = $tag;
                $value = null;
            }

            $formatted[] = [
                'raw' => $tag,
                'name' => $name,
                'value' => $value,
            ];
        }

        return $formatted;
    }

    /**
     * Update tags of the given entry.
     *
     * @return array
     */
    protected function updateTags(EntryUpdate $update, array $tags)
    {
        if (! empty($update->tagsChanges['added'])) {
            $tags = array_unique(
                array_merge($tags, $update->tagsChanges['added'])
            );
        }

        if (! empty($update->tagsChanges['removed'])) {
            Arr::forget($tags, $update->tagsChanges['removed']);
        }

        return $tags;
    }
}
