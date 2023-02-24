<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Sentry\Listener;

use FriendsOfHyperf\Http\RequestLifeCycle\Events\RequestReceived;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;
use Sentry\ClientBuilderInterface;
use Sentry\SentrySdk;
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
            MainCoroutineServerStart::class,
            RequestReceived::class,
        ];
    }

    /**
     * @param AfterWorkerStart|MainCoroutineServerStart|RequestReceived $event
     */
    public function process(object $event): void
    {
        SentrySdk::setCurrentHub(
            tap(
                make(HubInterface::class),
                fn ($hub) => $hub->bindClient($this->container->get(ClientBuilderInterface::class)->getClient())
            )
        );
    }
}
