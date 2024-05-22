<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Events;

use FriendsOfHyperf\Notifications\Enums\NotificationSendingStatusEnum;
use FriendsOfHyperf\Notifications\Notification;

class NotificationSending
{
    public function __construct(
        public mixed $notifiable,
        public Notification $notification,
        public string $channel,
        public NotificationSendingStatusEnum $status = NotificationSendingStatusEnum::ENABLED,
    ) {
    }
}
