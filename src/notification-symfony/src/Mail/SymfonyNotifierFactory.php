<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Symfony\Mail;

use Psr\Container\ContainerInterface;
use Symfony\Component\Notifier\Channel\ChannelPolicyInterface;
use Symfony\Component\Notifier\Notifier;

class SymfonyNotifierFactory
{
    public function __construct(
        private ContainerInterface $container
    ) {
    }

    public function __invoke(): Notifier
    {
        $channelPolicy = $this->container->has(ChannelPolicyInterface::class) ? $this->container->get(ChannelPolicyInterface::class) : null;
        return new Notifier($this->container, $channelPolicy);
    }
}
