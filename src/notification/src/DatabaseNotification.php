<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification;

use Carbon\Carbon;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\MorphTo;

/**
 * @property null|Carbon $read_at
 */
class DatabaseNotification extends Model
{
    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public bool $incrementing = false;

    /**
     * The "type" of the primary key ID.
     */
    protected string $keyType = 'string';

    /**
     * The table associated with the model.
     */
    protected ?string $table = 'notifications';

    /**
     * The guarded attributes on the model.
     */
    protected array $guarded = [];

    /**
     * The attributes that should be cast to native types.
     */
    protected array $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Get the notifiable entity that the notification belongs to.
     */
    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }

    /**
     * Mark the notification as unread.
     */
    public function markAsUnread(): void
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    /**
     * Determine if a notification has been read.
     */
    public function read(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Determine if a notification has not been read.
     */
    public function unread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Scope a query to only include read notifications.
     */
    public function scopeRead(Builder $query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread(Builder $query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Create a new database notification collection instance.
     */
    public function newCollection(array $models = [])
    {
        return new DatabaseNotificationCollection($models);
    }
}
