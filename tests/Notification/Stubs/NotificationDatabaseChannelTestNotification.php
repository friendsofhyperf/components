<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\Notification\Stubs;

use FriendsOfHyperf\Notification\Message\DatabaseMessage;
use FriendsOfHyperf\Notification\Notification;

class NotificationDatabaseChannelTestNotification extends Notification
{
    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'message' => 'Hello World',
        ]);
    }
}
