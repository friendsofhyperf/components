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

use FriendsOfHyperf\Notifications\Events\AnonymousNotifiableEvent;
use Hyperf\Context\ApplicationContext;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;

class AnonymousNotifiable
{
    /**
     * All of the notification routing information.
     */
    public array $routes = [];

    /**
     * Add routing information to the target.
     */
    public function route(string $channel, mixed $route): static
    {
        if ($channel === 'database') {
            throw new InvalidArgumentException('The database channel does not support on-demand notifications.');
        }

        $this->routes[$channel] = $route;

        return $this;
    }

    /**
     * Send the given notification.
     */
    public function notify(mixed $notification): void
    {
        $this->getDispatcher()->dispatch(new AnonymousNotifiableEvent($notification, 'send'));
    }

    /**
     * Send the given notification immediately.
     */
    public function notifyNow(mixed $notification): void
    {
        $this->getDispatcher()->dispatch(new AnonymousNotifiableEvent($notification, 'sendNow'));
    }

    /**
     * Get the notification routing information for the given driver.
     */
    public function routeNotificationFor(string $driver): mixed
    {
        return $this->routes[$driver] ?? null;
    }

    private function getDispatcher(): EventDispatcherInterface
    {
        return ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
    }
}
