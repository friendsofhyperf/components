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
}
