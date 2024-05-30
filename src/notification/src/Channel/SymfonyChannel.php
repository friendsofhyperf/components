<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Channel;

use FriendsOfHyperf\Notification\Contract\Channel as ChannelContract;
use FriendsOfHyperf\Notification\Notification;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Notifier\NotifierInterface;

class SymfonyChannel implements ChannelContract
{
    protected ?NotifierInterface $notifier = null;

    public function __construct(
        protected ContainerInterface $container
    ) {
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        if (! method_exists($notification, 'toSymfony') || ! method_exists($notification, 'recipient')) {
            throw new RuntimeException('Notification must implement `toSymfony` method.');
        }

        $this->getNotifier()->send(
            $notification->toSymfony($notifiable),
            $notification->recipient($notifiable)
        );

        return null;
    }

    public function getNotifier(): NotifierInterface
    {
        if (! $this->notifier) {
            if (! $this->container->has(NotifierInterface::class)) {
                throw new RuntimeException('Please bind `Symfony\Component\Notifier\NotifierInterface` to container first.');
            }

            $this->notifier = $this->container->get(NotifierInterface::class);
        }
        return $this->notifier;
    }
}
