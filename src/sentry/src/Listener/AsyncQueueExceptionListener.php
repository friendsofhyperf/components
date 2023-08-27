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

use Hyperf\AsyncQueue\Event;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

use function Hyperf\Support\make;

class AsyncQueueExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\BeforeHandle::class,
            Event\FailedHandle::class,
        ];
    }

    /**
     * @param Event\FailedHandle $event
     */
    public function process(object $event): void
    {
        if (! $this->isEnable('async_queue')) {
            return;
        }

        match ($event::class) {
            Event\FailedHandle::class => $this->captureException($event->getThrowable()),
            default => SentrySdk::setCurrentHub(make(HubInterface::class)),
        };
    }
}
