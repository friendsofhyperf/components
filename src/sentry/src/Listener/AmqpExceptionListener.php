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
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

use function Hyperf\Support\make;

class AmqpExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\BeforeConsume::class,
            Event\FailToConsume::class,
        ];
    }

    /**
     * @param Event\FailToConsume $event
     */
    public function process(object $event): void
    {
        if (! $this->isEnable('amqp')) {
            return;
        }

        match ($event::class) {
            Event\FailToConsume::class => $this->captureException($event->getThrowable()),
            default => SentrySdk::setCurrentHub(make(HubInterface::class)),
        };
    }
}
