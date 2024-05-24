<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Listener;

use FriendsOfHyperf\Notification\Annotation\Channel;
use FriendsOfHyperf\Notification\Channel\DatabaseChannel;
use FriendsOfHyperf\Notification\Channel\SmsChannel;
use FriendsOfHyperf\Notification\ChannelManager;
use FriendsOfHyperf\Notification\Contract\Channel as ChannelContract;
use Hyperf\Context\ApplicationContext;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterChannelsListener implements ListenerInterface
{
    public function getChannelManager(): ChannelManager
    {
        return ApplicationContext::getContainer()->get(ChannelManager::class);
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var array<class-string<ChannelContract>,Channel> $channels */
        $channels = AnnotationCollector::getClassesByAnnotation(Channel::class);

        foreach ($channels as $channelClass => $annotation) {
            if (! is_a($channelClass, Channel::class, true)) {
                continue;
            }

            $this->getChannelManager()->register($annotation->name, $channelClass);
        }

        /*
         * Register default channels.
         */

        $this->getChannelManager()->register('database', DatabaseChannel::class);
        $this->getChannelManager()->register('sms', SmsChannel::class);
    }
}
