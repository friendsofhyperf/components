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

use FriendsOfHyperf\Notification\Contract\Channel;
use FriendsOfHyperf\Notification\Contract\Dispatcher;
use Hyperf\Contract\TranslatorInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ChannelManager implements Dispatcher
{
    /**
     * @var array<string, Channel>
     */
    protected static array $channels = [];

    public function __construct(
        protected ContainerInterface $container,
        protected EventDispatcherInterface $dispatcher,
        protected TranslatorInterface $translator
    ) {
    }

    /**
     * Send the given notification to the given notifiable entities.
     */
    public function send(mixed $notifiables, Notification $notification): void
    {
        (
            new NotificationSender(
                $this,
                $this->dispatcher,
                $this->translator
            )
        )->send($notifiables, $notification);
    }

    /**
     * @param class-string<Channel> $class
     */
    public function register(string $name, string $class): void
    {
        static::$channels[$name] = $this->container->get($class);
    }

    /**
     * Get the channel.
     */
    public function channel(string $channel): Channel
    {
        if (! isset(static::$channels[$channel])) {
            throw new InvalidArgumentException("Channel [{$channel}] is not defined.");
        }

        return static::$channels[$channel];
    }
}
