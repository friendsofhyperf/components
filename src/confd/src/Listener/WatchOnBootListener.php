<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Confd\Listener;

use FriendsOfHyperf\Confd\Confd;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\MainWorkerStart;
use Psr\Container\ContainerInterface;

#[Listener()]
class WatchOnBootListener implements ListenerInterface
{
    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private Confd $confd, private StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            MainWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->confd->watch();
        $this->logger->debug('[confd] Start watching.');

        while (true) {
            $isWorkerExited = CoordinatorManager::until(Constants::WORKER_EXIT)->yield(1);

            if ($isWorkerExited) {
                break;
            }
        }
    }
}
