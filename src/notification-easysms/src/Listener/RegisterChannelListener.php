<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\EasySms\Listener;

use FriendsOfHyperf\Notification\ChannelManager;
use FriendsOfHyperf\Notification\EasySms\Channel\EasySmsChannel;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterChannelListener implements ListenerInterface
{
    public function __construct(
        protected ChannelManager $channelManager
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
        $this->channelManager->register('easy-sms', EasySmsChannel::class);
    }
}
