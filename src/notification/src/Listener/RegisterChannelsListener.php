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
use FriendsOfHyperf\Notification\ChannelManager;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterChannelsListener implements ListenerInterface
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
        /** @var array<class-string,Channel> $channels */
        $channels = AnnotationCollector::getClassesByAnnotation(Channel::class);

        foreach ($channels as $channelClass => $annotation) {
            if (! is_a($channelClass, Channel::class, true)) {
                continue;
            }

            $this->channelManager->register($annotation->name, $channelClass);
        }

        $this->channelManager->register('database', DatabaseChannel::class);
    }
}
