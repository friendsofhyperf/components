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

use FriendsOfHyperf\Notification\Contract\Channel;
use FriendsOfHyperf\Notification\Notification;
use Symfony\Component\Notifier\Channel\ChannelInterface;

abstract class AbstractSymfonyChannel implements Channel
{
    public function __construct(
        private ChannelInterface $channel,
        private string $name
    ) {
    }

    public function send(mixed $notifiable, Notification $notification): mixed
    {
        $this->channel->notify($notification, $notifiable, $this->name);
    }
}
