<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/sentry.
 *
 * @link     https://github.com/friendsofhyperf/sentry
 * @document https://github.com/friendsofhyperf/sentry/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Psr\Container\ContainerInterface;
use Sentry\State\HubInterface;

class InitHubListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
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
