<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Model;

use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;
use Hyperf\Database\Model\Collection;

use function Hyperf\Collection\collect;

/**
 * @property string $id
 * @property int $sequence
 * @property string $uuid
 * @property string $batch_id
 * @property string $sub_batch_id
 * @property string $family_hash
 * @property bool $should_display_on_index
 * @property string $type
 * @property array $content
 * @property Collection<int,EntryTagModel> $tags
 * @property \Carbon\Carbon $created_at
 * @method static \Hyperf\Database\Model\Builder withTelescopeOptions(string $type, EntryQueryOptions $options = null)
 */
class EntryModel extends Model
{
    /**
     * The name of the "updated at" column.
     *
     * @var ?string
     */
    public const UPDATED_AT = null;

    /**
     * Prevent Eloquent from overriding uuid with `lastInsertId`.
     */
    public bool $incrementing = false;

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'telescope_entries';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
        'sequence',
        'uuid',
        'batch_id',
        'sub_batch_id',
        'family_hash',
        'should_display_on_index',
        'type',
        'content',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'content' => 'json',
    ];

    /**
     * The primary key for the model.
     */
    protected string $primaryKey = 'uuid';

    /**
     * The "type" of the auto-incrementing ID.
     */
    protected string $keyType = 'string';

    protected array $appends = ['id'];

    public function getIdAttribute()
    {
        return $this->uuid ?? null;
    }

    public function tags()
    {
        return $this->hasMany(EntryTagModel::class, 'entry_uuid', 'uuid');
    }

    /**
     * Scope the query for the given query options.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @param string $type
     * @return \Hyperf\Database\Model\Builder
     */
    public function scopeWithTelescopeOptions($query, $type, EntryQueryOptions $options)
    {
        $this->whereType($query, $type)
            ->whereBatchId($query, $options)
            ->whereTag($query, $options)
            ->whereFamilyHash($query, $options)
            ->whereBeforeSequence($query, $options)
            ->filter($query, $options);

        return $query;
    }

    /**
     * Scope the query for the given type.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @param string $type
     * @return $this
     */
    protected function whereType($query, $type)
    {
        $query->when($type, function ($query, $type) {
            return $query->where('type', $type);
        });

        return $this;
    }

    /**
     * Scope the query for the given batch ID.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @return $this
     */
    protected function whereBatchId($query, EntryQueryOptions $options)
    {
        $query->when($options->batchId, function ($query, $batchId) {
            return $query->where('batch_id', $batchId);
        });

        return $this;
    }

    /**
     * Scope the query for the given type.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @return $this
     */
    protected function whereTag($query, EntryQueryOptions $options)
    {
        $query->when($options->tag, function ($query, $tag) {
            $tags = collect(explode(',', $tag))->map(fn ($tag) => trim($tag));

            if ($tags->isEmpty()) {
                return $query;
            }

            return $query->whereIn('uuid', function ($query) use ($tags) {
                $query->select('entry_uuid')->from('telescope_entries_tags')
                    ->whereIn('entry_uuid', function ($query) use ($tags) {
                        $query->select('entry_uuid')->from('telescope_entries_tags')->whereIn('tag', $tags->all());
                    });
            });
        });

        return $this;
    }

    /**
     * Scope the query for the given type.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @return $this
     */
    protected function whereFamilyHash($query, EntryQueryOptions $options)
    {
        $query->when($options->familyHash, function ($query, $hash) {
            return $query->where('family_hash', $hash);
        });

        return $this;
    }

    /**
     * Scope the query for the given pagination options.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @return $this
     */
    protected function whereBeforeSequence($query, EntryQueryOptions $options)
    {
        $query->when($options->beforeSequence, function ($query, $beforeSequence) {
            return $query->where('sequence', '<', $beforeSequence);
        });

        return $this;
    }

    /**
     * Scope the query for the given display options.
     *
     * @param \Hyperf\Database\Model\Builder $query
     * @return $this
     */
    protected function filter($query, EntryQueryOptions $options)
    {
        if ($options->familyHash || $options->tag || $options->batchId) {
            return $this;
        }

        $query->where('should_display_on_index', true);

        return $this;
    }
}
