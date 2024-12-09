<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use Carbon\Carbon;
use FriendsOfHyperf\Telescope\Contract\EntriesRepository;
use FriendsOfHyperf\Telescope\Model\EntryModel;
use FriendsOfHyperf\Telescope\Model\EntryTagModel;
use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;

class IncomingEntry
{
    /**
     * The entry's UUID.
     *
     * @var string
     */
    public $uuid = '';

    /**
     * The entry's batch ID.
     *
     * @var string
     */
    public $batchId = '';

    /**
     * The entry's sub batch ID.
     *
     * @var string
     */
    public $subBatchId = '';

    /**
     * The entry's type.
     *
     * @var string
     */
    public $type = '';

    /**
     * The entry's family hash.
     *
     * @var string|null
     */
    public $familyHash;

    /**
     * The currently authenticated user, if applicable.
     *
     * @var mixed
     */
    public $user;

    /**
     * The entry's content.
     *
     * @var array
     */
    public $content = [];

    /**
     * The entry's tags.
     *
     * @var array
     */
    public $tags = [];

    /**
     * The DateTime that indicates when the entry was recorded.
     *
     * @var string
     */
    public $recordedAt = '';

    /**
     * Create a new incoming entry instance.
     */
    public function __construct(array $content)
    {
        $this->uuid = (string) Str::orderedUuid()->toString();
        $timezone = Telescope::getConfig()->getTimezone();
        $this->recordedAt = Carbon::now()->setTimezone($timezone)->toDateTimeString();
        $this->content = array_merge($content, ['hostname' => $hostname = gethostname()]);
        $this->tags = [
            'hostname:' . $hostname,
            'app_name:' . Telescope::getConfig()->getAppName(),
        ];
    }

    /**
     * Create a new entry instance.
     *
     * @param mixed ...$arguments
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    /**
     * Assign the entry a given batch ID.
     *
     * @return $this
     */
    public function batchId(string $batchId): static
    {
        $this->batchId = $batchId;

        return $this;
    }

    /**
     * Assign the entry a given sub batch ID.
     *
     * @return $this
     */
    public function subBatchId(string $batchId): static
    {
        $this->subBatchId = $batchId;

        return $this;
    }

    /**
     * Assign the entry a given type.
     *
     * @return $this
     */
    public function type(string $type): static
    {
        $this->type = $type;

        if ($type == EntryType::QUERY && $this->content['slow']) {
            $this->tags(['slow']);
        }

        return $this;
    }

    /**
     * Assign the entry a family hash.
     *
     * @return $this
     */
    public function withFamilyHash(string $familyHash): static
    {
        $this->familyHash = $familyHash;

        return $this;
    }

    /**
     * Set the currently authenticated user.
     *
     * @param object $user
     * @return $this
     */
    public function user($user = null): static
    {
        // to do
        return $this;
    }

    /**
     * Merge tags into the entry's existing tags.
     *
     * @return $this
     */
    public function tags(array $tags): static
    {
        $this->tags = array_unique(array_merge($this->tags, $tags));

        return $this;
    }

    /**
     * Determine if the incoming entry has a monitored tag.
     */
    public function hasMonitoredTag(): bool
    {
        if (! empty($this->tags)) {
            return ApplicationContext::getContainer()->get(EntriesRepository::class)->isMonitoring($this->tags);
        }

        return false;
    }

    /**
     * Determine if the incoming entry is a failed request.
     */
    public function isFailedRequest(): bool
    {
        return $this->type === EntryType::REQUEST
            && ($this->content['response_status'] ?? 200) >= 500;
    }

    /**
     * Determine if the incoming entry is a query.
     */
    public function isQuery(): bool
    {
        return $this->type === EntryType::QUERY;
    }

    /**
     * Determine if the incoming entry is a failed job.
     */
    public function isFailedJob(): bool
    {
        return $this->type === EntryType::JOB
            && ($this->content['status'] ?? null) === 'failed';
    }

    /**
     * Determine if the incoming entry is a reportable exception.
     */
    public function isReportableException(): bool
    {
        return false;
    }

    /**
     * Determine if the incoming entry is an exception.
     */
    public function isException(): bool
    {
        return false;
    }

    /**
     * Determine if the incoming entry is a dump.
     */
    public function isDump(): bool
    {
        return false;
    }

    /**
     * Determine if the incoming entry is a log entry.
     *
     * @return bool
     */
    public function isLog()
    {
        return $this->type === EntryType::LOG;
    }

    /**
     * Determine if the incoming entry is a scheduled task.
     */
    public function isScheduledTask(): bool
    {
        return $this->type === EntryType::SCHEDULED_TASK;
    }

    /**
     * Get the family look-up hash for the incoming entry.
     */
    public function familyHash(): ?string
    {
        return $this->familyHash;
    }

    /**
     * Get an array representation of the entry for storage.
     */
    public function toArray(): array
    {
        $content = json_decode(json_encode($this->content, JSON_INVALID_UTF8_SUBSTITUTE), true);

        return [
            'uuid' => $this->uuid,
            'batch_id' => $this->batchId,
            'sub_batch_id' => $this->subBatchId,
            'family_hash' => $this->familyHash,
            'type' => $this->type,
            'content' => $content ?: [],
            'created_at' => $this->recordedAt,
        ];
    }

    /**
     * @deprecated since v3.1, use `store()`, will be removed in v3.2
     */
    public function create(): void
    {
        EntryModel::query()->create($this->toArray());

        foreach ($this->tags as $tag) {
            EntryTagModel::query()->create([
                'entry_uuid' => $this->uuid,
                'tag' => $tag,
            ]);
        }
    }
}
