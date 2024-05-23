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

use FriendsOfHyperf\Notification\Attributes\Channel;
use FriendsOfHyperf\Notification\Contract\Channel as ChannelContract;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\TranslatorInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ChannelManager
{
    /**
     * @var array<string, Channel>
     */
    protected array $channels = [];

    public function __construct(
        protected ContainerInterface $container,
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

    public function register(string $name, string $class): void
    {
        $this->channels[$name] = $this->container->get($class);
    }

    public function get(string $name): Channel
    {
        return $this->channels[$name];
    }
}
