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

use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\RpcServer\Event\RequestReceived as RpcRequestReceived;
use Hyperf\RpcServer\Event\RequestTerminated as RpcRequestTerminated;

class RequestExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            RequestReceived::class,
            RequestTerminated::class,
            RpcRequestReceived::class,
            RpcRequestTerminated::class,
        ];
    }

    /**
     * @param RequestTerminated|object $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isEnable('request')) {
            return;
        }

        match ($event::class) {
            RequestTerminated::class, RpcRequestTerminated::class => $this->captureException($event->exception),
            default => $this->setupSentrySdk(),
        };
    }
}
