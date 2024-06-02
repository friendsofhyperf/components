<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Channel;

use FriendsOfHyperf\Notification\Contract\Channel;
use FriendsOfHyperf\Notification\Notification;
use FriendsOfHyperf\Notification\Symfony\Contract\SymfonyMessage;
use InvalidArgumentException;
use Symfony\Component\Notifier\NotifierInterface;

class SymfonyChannel implements Channel
{
    public function __construct(
        private readonly NotifierInterface $notifier
    ) {
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        if (! $notification instanceof SymfonyMessage) {
            throw new InvalidArgumentException('Notification must be an instance of SymfonyContract');
        }
        $symfonyNotification = $notification->symfony($notifiable);
        $recipients = $notification->recipients($notifiable);
        $this->notifier->send($symfonyNotification, $recipients);
        return true;
    }
}
