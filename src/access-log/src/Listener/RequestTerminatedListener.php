<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\AccessLog\Listener;

use FriendsOfHyperf\AccessLog\Handler;
use FriendsOfHyperf\HttpRequestLifeCycle\Events\RequestTerminated;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
#[Listener]
class RequestTerminatedListener implements ListenerInterface
{
    /**
     * @var Handler
     */
    protected $handler;

    public function __construct(Handler $handler)
    {
        $this->handler = $handler;
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
