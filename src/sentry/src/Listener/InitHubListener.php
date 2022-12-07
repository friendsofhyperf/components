<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Psr\Container\ContainerInterface;
use Sentry\State\HubInterface;

class InitHubListener implements ListenerInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            AfterWorkerStart::class,
        ];
    }

    /**
     * @param AfterWorkerStart $event
     */
    public function process(object $event): void
    {
        $this->container->get(HubInterface::class);
    }
}
