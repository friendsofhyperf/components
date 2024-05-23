<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification;

use FriendsOfHyperf\Notification\Contract\Dispatcher;
use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;

trait RoutesNotifications
{
    /**
     * Send the given notification.
     */
    public function notify(Notification $instance): void
    {
        ApplicationContext::getContainer()->get(Dispatcher::class)->send($this, $instance);
    }

    /**
     * Get the notification routing information for the given driver.
     */
    public function routeNotificationFor(string $driver, ?Notification $notification = null): mixed
    {
        if (method_exists($this, $method = 'routeNotificationFor' . Str::studly($driver))) {
            return $this->{$method}($notification);
        }

        return match ($driver) {
            'database' => $this->notifications(),
            'mail' => $this->email,
            default => null,
        };
    }
}
