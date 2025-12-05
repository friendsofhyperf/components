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

use Closure;
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
    protected array $channels = [];

    public function __construct(
        protected ContainerInterface $container,
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
                $this->container->get(EventDispatcherInterface::class),
                $this->translator
            )
        )->send($notifiables, $notification);
    }

    /**
     * @param class-string<Channel>|Channel|(Closure():Channel) $class
     */
    public function register(string $name, string|Channel|Closure $class, bool $replace = false): void
    {
        if (! $replace && isset($this->channels[$name])) {
            throw new InvalidArgumentException("Channel [{$name}] is already defined.");
        }

        $instance = match (true) {
            $class instanceof Closure => $class(),
            $class instanceof Channel => $class,
            is_string($class) && is_a($class, Channel::class, true) => $this->container->get($class),
            default => null,
        };

        if (! $instance instanceof Channel) {
            throw new InvalidArgumentException('Invalid channel.');
        }

        $this->channels[$name] = $instance;
    }

    /**
     * Get the channel.
     */
    public function channel(string $channel): Channel
    {
        if (! isset($this->channels[$channel])) {
            throw new InvalidArgumentException("Channel [{$channel}] is not defined.");
        }

        return $this->channels[$channel];
    }
}
