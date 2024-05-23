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

use Hyperf\Contract\TranslatorInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class NotificationDispatcher implements Contract\Dispatcher
{
    public function __construct(
        protected ChannelManager $channelManager,
        protected EventDispatcherInterface $dispatcher,
        protected TranslatorInterface $translator
    ) {
    }

    /**
     * Send the given notification to the given notifiable entities.
     */
    public function send(mixed $notifiables, Notification $notification): void
    {
        (new NotificationSender(
            $this->channelManager,
            $this->dispatcher,
            $this->translator
        )
        )->send($notifiables, $notification);
    }
}
