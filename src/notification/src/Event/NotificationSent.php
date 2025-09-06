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

use FriendsOfHyperf\Notification\Notification;

class NotificationSent
{
    public function __construct(
        public readonly mixed $notifiable,
        public readonly Notification $notification,
        public readonly string $channel,
        public readonly mixed $response
    ) {
    }
}
