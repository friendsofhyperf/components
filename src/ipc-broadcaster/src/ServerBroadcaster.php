<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster;

use FriendsOfHyperf\IpcBroadcaster\Contract\BroadcasterInterface;
use FriendsOfHyperf\IpcBroadcaster\Contract\IpcMessageInterface;
use Psr\Container\ContainerInterface;
use Swoole\Server;

use function Hyperf\Support\class_uses_recursive;

class ServerBroadcaster implements BroadcasterInterface
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @param null|int $id WorkerId
     */
    public function __construct(
        private ContainerInterface $container,
        protected ?int $id = null
    ) {
    }

    public function broadcast(IpcMessageInterface $message): void
    {
        /** @var IpcMessageInterface|mixed $message */
        if (
            in_array(Traits\RunsInCurrentWorker::class, class_uses_recursive($message))
            && ! $message->hasRun()
        ) {
            $message->handle();
            $message->setHasRun(true);
        }

        if (Constant::isCoroutineServer()) {
            return;
        }

        // Lazy load to avoid causing issue before sever starts.
        if ($this->server === null) {
            $this->server = $this->container->get(Server::class);
        }

        if ($this->id !== null) {
            $this->server->sendMessage($message, $this->id);
            return;
        }

        $workerCount = $this->server->setting['worker_num'] - 1;
        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId === $this->server->worker_id) {
                continue;
            }
            $this->server->sendMessage($message, $workerId);
        }
    }
}
