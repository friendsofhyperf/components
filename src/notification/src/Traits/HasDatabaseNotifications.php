<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Traits;

use FriendsOfHyperf\Notification\DatabaseNotification;
use Hyperf\Database\Model\Model;
use Hyperf\Database\Model\Relations\MorphMany;

/**
 * @mixin Model
 */
trait HasDatabaseNotifications
{
    /**
     * Get the entity's notifications.
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->latest();
    }

    /**
     * Get the entity's read notifications.
     */
    public function readNotifications(): MorphMany
    {
        return $this->notifications()->read();
    }

    /**
     * Get the entity's unread notifications.
     */
    public function unreadNotifications(): MorphMany
    {
        return $this->notifications()->unread();
    }
}
