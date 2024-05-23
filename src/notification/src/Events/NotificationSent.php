<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Events;

use FriendsOfHyperf\Notification\Notification;

class NotificationSent
{
    public function __construct(
        public mixed $notifiable,
        public Notification $notification,
        public string $channel,
        public mixed $response
    ) {
    }
}
