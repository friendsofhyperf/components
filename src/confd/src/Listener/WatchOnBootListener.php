<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd\Listener;

use FriendsOfHyperf\Confd\Confd;
use FriendsOfHyperf\Confd\Traits\Logger;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;

class WatchOnBootListener implements ListenerInterface
{
    use Logger;

    public function __construct(private ContainerInterface $container)
    {
        $this->resolveLogger();
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
            MainCoroutineServerStart::class,
        ];
    }

    /**
     * @param MainCoroutineServerStart|MainWorkerStart $event
     */
    public function process(object $event): void
    {
        $this->container->get(Confd::class)->watch();
        $this->logger?->debug('[confd] Start watching.');
    }
}
