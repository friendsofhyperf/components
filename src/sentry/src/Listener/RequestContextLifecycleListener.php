<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event as HttpEvent;
use Hyperf\RpcServer\Event as RpcEvent;
use Sentry\SentrySdk;

use function Hyperf\Coroutine\defer;

class RequestContextLifecycleListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            HttpEvent\RequestReceived::class,
            RpcEvent\RequestReceived::class,
        ];
    }

    public function process(object $event): void
    {
        $previousContext = SentrySdk::getCurrentRuntimeContext();
        SentrySdk::startContext();

        if (SentrySdk::getCurrentRuntimeContext() === $previousContext) {
            return;
        }

        // Register cleanup before the remaining request listeners add their deferred
        // callbacks. Coroutine defers run in LIFO order, so the context is ended last.
        defer(static fn () => SentrySdk::endContext());
    }
}
