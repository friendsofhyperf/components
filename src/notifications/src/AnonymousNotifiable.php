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

use FriendsOfHyperf\Notifications\Contract\Dispatcher;
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
    public function notify(Notification $notification): void
    {
        $this->getDispatcher()->send($this,$notification);
    }


    /**
     * Get the notification routing information for the given driver.
     */
    public function routeNotificationFor(string $driver): mixed
    {
        return $this->routes[$driver] ?? null;
    }

    private function getDispatcher(): Dispatcher
    {
        return ApplicationContext::getContainer()->get(Dispatcher::class);
    }
}
