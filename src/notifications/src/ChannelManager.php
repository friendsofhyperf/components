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

use FriendsOfHyperf\Notifications\Attributes\Channel;
use FriendsOfHyperf\Notifications\Contract\Channel as ChannelContract;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;

class ChannelManager
{
    public function __construct(
        public EventDispatcherInterface $dispatcher,
        public TranslatorInterface $translator
    ) {
    }

    /**
     * Send the given notification to the given notifiable entities.
     */
    public function send(mixed $notifiables, Notification $notification): void
    {
        (new NotificationSender(
            $this,
            $this->dispatcher,
            $this->translator
        )
        )->send($notifiables, $notification);
    }

    /**
     * Get the channel.
     */
    public function channel(string $channel): ChannelContract
    {
        $channelClass = Channel::get($channel);
        if (! class_exists($channelClass)) {
            throw new InvalidArgumentException("Channel [{$channel}] is not defined.");
        }
        return ApplicationContext::getContainer()->get($channelClass);
    }
}
