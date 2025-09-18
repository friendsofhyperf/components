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

class AsyncQueueExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\BeforeHandle::class,
            Event\AfterHandle::class,
            Event\RetryHandle::class,
            Event\FailedHandle::class,
        ];
    }

    /**
     * @param Event\FailedHandle|Event\AfterHandle|Event\BeforeHandle|object $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isEnable('async_queue')) {
            return;
        }

        match ($event::class) {
            Event\RetryHandle::class,
            Event\FailedHandle::class => $this->captureException($event->getThrowable()),
            default => $this->setupSentrySdk(),
        };

        match ($event::class) {
            Event\AfterHandle::class,
            Event\RetryHandle::class,
            Event\FailedHandle::class => $this->flushEvents(),
            default => null,
        };
    }
}
