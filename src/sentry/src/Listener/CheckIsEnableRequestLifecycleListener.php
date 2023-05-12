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

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Server\Port;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;

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
        /** @var Port[] $ports */
        $ports = $this->container->get(ServerFactory::class)->getConfig()?->getServers();

        if (! $ports) {
            return;
        }

        foreach ($ports as $port) {
            if ($port->getOptions()?->isEnableRequestLifecycle()) {
                return;
            }
        }

        $this->container->get(StdoutLoggerInterface::class)->warning('Sentry: Request lifecycle is not enabled, please set `enable_request_lifecycle` to true in server config.');
    }
}
