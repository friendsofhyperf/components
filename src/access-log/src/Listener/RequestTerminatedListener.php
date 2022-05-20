<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/access-log.
 *
 * @link     https://github.com/friendsofhyperf/access-log
 * @document https://github.com/friendsofhyperf/access-log/blob/main/README.md
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
    public function process(object $event)
    {
        $this->handler->process($event->request, $event->response);
    }
}
