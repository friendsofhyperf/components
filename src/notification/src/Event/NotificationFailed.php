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

class NotificationFailed
{
    public function __construct(
        public mixed $notifiable,
        public mixed $notification,
        public string $channel,
        public array $data
    ) {
    }
}
