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

use Hyperf\Amqp\Event;

class AmqpExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\BeforeConsume::class,
            Event\AfterConsume::class,
            Event\FailToConsume::class,
        ];
    }

    /**
     * @param Event\FailToConsume|Event\AfterConsume|Event\BeforeConsume|object $event
     */
    public function process(object $event): void
    {
        if (! $this->switcher->isEnable('amqp')) {
            return;
        }

        match ($event::class) {
            Event\FailToConsume::class => $this->captureException($event->getThrowable()),
            default => $this->setupSentrySdk(),
        };

        match ($event::class) {
            Event\AfterConsume::class,
            Event\FailToConsume::class => $this->flushEvents(),
            default => null,
        };
    }
}
