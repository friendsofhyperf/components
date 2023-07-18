<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AccessLog\Listener;

use FriendsOfHyperf\AccessLog\Handler;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\RequestTerminated;

class RequestTerminatedListener implements ListenerInterface
{
    public function __construct(protected Handler $handler)
    {
    }

    public function listen(): array
    {
        return [
            RequestTerminated::class,
        ];
    }

    /**
     * @param RequestTerminated $event
     */
    public function process(object $event): void
    {
        $this->handler->process($event->request, $event->response);
    }
}
