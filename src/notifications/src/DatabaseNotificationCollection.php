<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications;

use Hyperf\Database\Model\Collection;

class DatabaseNotificationCollection extends Collection
{
    /**
     * Mark all notifications as read.
     */
    public function markAsRead(): void
    {
        // @phpstan-ignore-next-line
        $this->each->markAsRead();
    }

    /**
     * Mark all notifications as unread.
     */
    public function markAsUnread(): void
    {
        // @phpstan-ignore-next-line
        $this->each->markAsUnread();
    }
}
