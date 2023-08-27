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

use Hyperf\HttpServer\Event;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

use function Hyperf\Support\make;

class RequestExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\RequestReceived::class,
            Event\RequestTerminated::class,
        ];
    }

    /**
     * @param Event\RequestTerminated $event
     */
    public function process(object $event): void
    {
        if (! $this->isEnable('request')) {
            return;
        }

        match ($event::class) {
            Event\RequestTerminated::class => $this->captureException($event->exception),
            default => SentrySdk::setCurrentHub(make(HubInterface::class)),
        };
    }
}
