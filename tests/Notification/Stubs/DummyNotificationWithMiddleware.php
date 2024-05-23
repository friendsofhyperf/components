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

use FriendsOfHyperf\Notification\Notification;

class DummyNotificationWithMiddleware extends Notification
{
    public function via($notifiable)
    {
        return 'mail';
    }

    public function middleware()
    {
        return [
            new TestNotificationMiddleware(),
        ];
    }
}
