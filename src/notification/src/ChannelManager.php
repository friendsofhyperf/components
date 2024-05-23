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
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class ChannelManager
{
    /**
     * @var array<string, Channel>
     */
    protected array $channels = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(string $name, string $class): void
    {
        $this->channels[$name] = $this->container->get($class);
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
