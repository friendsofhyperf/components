<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AsyncTask\Listener;

use FriendsOfHyperf\AsyncTask\Task;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;

class BindServerAndWorkerIdListener implements ListenerInterface
{
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
        Task::$server = $event->server;
        Task::$workerId = $event->workerId;
    }
}
