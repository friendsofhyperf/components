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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\Metrics\CoroutineServerStats;
use FriendsOfHyperf\Sentry\Metrics\Timer;
use Hyperf\Engine\Coroutine as Co;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event as HttpEvent;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\RpcServer\Event as RpcEvent;
use Psr\Http\Message\ServerRequestInterface;

class RequestWatcher implements ListenerInterface
{
    public function __construct(
        protected CoroutineServerStats $stats,
        protected Feature $feature,
    ) {
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
        if (! $this->feature->isMetricsEnabled()) {
            return;
        }

        if ($event instanceof HttpEvent\RequestReceived || $event instanceof RpcEvent\RequestReceived) {
            ++$this->stats->accept_count;
            ++$this->stats->request_count;
            ++$this->stats->connection_num;

            $request = $event->request;
            $timer = Timer::make('http_requests', [
                'request_path' => $this->getPath($request),
                'request_method' => $request->getMethod(),
            ]);

            Co::defer(function () use ($timer) {
                ++$this->stats->close_count;
                ++$this->stats->response_count;
                --$this->stats->connection_num;

                $timer->end(true);
            });
        }
    }

    protected function getPath(ServerRequestInterface $request): string
    {
        $dispatched = $request->getAttribute(Dispatched::class);
        if (! $dispatched) {
            return $request->getUri()->getPath();
        }
        if (! $dispatched->handler) {
            return 'not_found';
        }
        return $dispatched->handler->route;
    }
}
