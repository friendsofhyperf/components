<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Mail\Listener;

use FriendsOfHyperf\Notification\ChannelManager;
use FriendsOfHyperf\Notification\Mail\Channel\MailChannel;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterChannelListener implements ListenerInterface
{
    public function __construct(
        private readonly ChannelManager $channelManager
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        // Register the mail channel
        $this->channelManager->register('mail', MailChannel::class);
    }
}
