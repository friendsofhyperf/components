<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Event;

use FriendsOfHyperf\Notification\Enums\NotificationSendingStatus;
use FriendsOfHyperf\Notification\Notification;

class NotificationSending
{
    public function __construct(
        public mixed $notifiable,
        public Notification $notification,
        public string $channel,
        public NotificationSendingStatus $status = NotificationSendingStatus::ENABLED,
    ) {
    }
}
