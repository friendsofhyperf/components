<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Metrics\Listener;

use FriendsOfHyperf\Sentry\Metrics\CoroutineServerStats;
use Hyperf\Engine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event as HttpEvent;
use Hyperf\RpcServer\Event as RpcEvent;

class RequestWatcher implements ListenerInterface
{
    public function __construct(protected CoroutineServerStats $stats)
    {
    }

    public function listen(): array
    {
        return [
            HttpEvent\RequestReceived::class,
            HttpEvent\RequestHandled::class,
            RpcEvent\RequestReceived::class,
            RpcEvent\RequestHandled::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof HttpEvent\RequestReceived || $event instanceof RpcEvent\RequestReceived) {
            ++$this->stats->accept_count;
            ++$this->stats->request_count;
            ++$this->stats->connection_num;

            Coroutine::defer(function () {
                ++$this->stats->close_count;
                ++$this->stats->response_count;
                --$this->stats->connection_num;
            });
        }
    }
}
