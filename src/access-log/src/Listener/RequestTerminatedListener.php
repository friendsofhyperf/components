<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AccessLog\Listener;

use FriendsOfHyperf\AccessLog\Handler;
use FriendsOfHyperf\Http\RequestLifeCycle\Events\RequestTerminated;
use Hyperf\Event\Contract\ListenerInterface;

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
