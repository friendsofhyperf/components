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

use Hyperf\Context\ApplicationContext;
use Hyperf\Stringable\Str;
use Psr\EventDispatcher\EventDispatcherInterface;

trait RoutesNotifications
{
    /**
     * Send the given notification.
     */
    public function notify(object $instance): void
    {
        $this->dispatch('notify', [$this, $instance]);
    }

    /**
     * Send the given notification immediately.
     */
    public function notifyNow(object $instance, ?array $channels = null): void
    {
        $this->dispatch('notifyNow', [$this, $instance, $channels]);
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

    /**
     * Send the given notification immediately.
     */
    private function dispatch(string $method, array $params): void
    {
        ApplicationContext::getContainer()
            ->get(EventDispatcherInterface::class)
            ->dispatch(
                new Events\NotifyEvent(
                    $method,
                    $params
                )
            );
    }
}
