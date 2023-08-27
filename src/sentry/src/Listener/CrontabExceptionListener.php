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

use Hyperf\Crontab\Event;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

use function Hyperf\Support\make;

class CrontabExceptionListener extends CaptureExceptionListener
{
    public function listen(): array
    {
        return [
            Event\BeforeExecute::class,
            Event\FailToExecute::class,
        ];
    }

    /**
     * @param Event\FailToExecute $event
     */
    public function process(object $event): void
    {
        if (! $this->isEnable('crontab')) {
            return;
        }

        match ($event::class) {
            Event\FailToExecute::class => $this->captureException($event->throwable),
            default => SentrySdk::setCurrentHub(make(HubInterface::class)),
        };
    }
}
