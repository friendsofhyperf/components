<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TcpSender\Listener;

use FriendsOfHyperf\TcpSender\Sender;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Psr\Container\ContainerInterface;

class InitSenderListener implements ListenerInterface
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            AfterWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        if ($this->container->has(Sender::class)) {
            $sender = $this->container->get(Sender::class);
            $sender->setWorkerId($event->workerId);
        }
    }
}
