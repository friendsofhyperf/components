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

use FriendsOfHyperf\Notification\Traits\RoutesNotifications;

class RoutesNotificationsTestInstance
{
    use RoutesNotifications;

    protected string $email = 'taylor@laravel.com';

    public function routeNotificationForFoo(): string
    {
        return 'bar';
    }
}
