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

use Hyperf\Command\Event;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

use function Hyperf\Support\make;

class CommandExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\BeforeHandle::class,
            Event\FailToHandle::class,
        ];
    }

    /**
     * @param Event\FailToHandle $event
     */
    public function process(object $event): void
    {
        if (! $this->isEnable('command')) {
            return;
        }

        match ($event::class) {
            Event\FailToHandle::class => $this->captureException($event->getThrowable()),
            default => SentrySdk::setCurrentHub(make(HubInterface::class)),
        };
    }
}
