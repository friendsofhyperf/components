<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\HttpServer\Event\RequestHandled;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Port;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class CheckIsEnableRequestLifecycleListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        if (! $this->hasRequestLifecycleListeners()) {
            return;
        }

        if ($this->isEnableRequestLifecycle()) {
            return;
        }

        $logger = $this->container->get(StdoutLoggerInterface::class);
        $logger->warning('Sentry: Request lifecycle is not enabled, please set `enable_request_lifecycle` to true in server config.');
    }

    protected function hasRequestLifecycleListeners(): bool
    {
        $listenerProvider = $this->container->get(ListenerProviderInterface::class);

        return count($listenerProvider->getListenersForEvent(new RequestReceived(null, null))) > 0
            || count($listenerProvider->getListenersForEvent(new RequestHandled(null, null))) > 0
            || count($listenerProvider->getListenersForEvent(new RequestTerminated(null, null))) > 0;
    }

    protected function isEnableRequestLifecycle(): bool
    {
        $serverFactory = $this->container->get(ServerFactory::class);
        /** @var Port[] $ports */
        $ports = $serverFactory->getConfig()?->getServers();

        if (! $ports) {
            return false;
        }

        foreach ($ports as $port) {
            if ($port->getOptions()?->isEnableRequestLifecycle()) {
                return true;
            }
        }

        return false;
    }
}
