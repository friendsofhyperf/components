<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Crontab\Event;
use Hyperf\Event\Contract\ListenerInterface;

class CronEventListener implements ListenerInterface
{
    public function __construct(
        private TelescopeConfig $telescopeConfig
    ) {
    }

    public function listen(): array
    {
        return [
            Event\AfterExecute::class,
            Event\FailToExecute::class,
        ];
    }

    /**
     * @param Event\AfterExecute|Event\FailToExecute|object $event
     */
    public function process(object $event): void
    {
        if (
            ! ($event instanceof Event\AfterExecute || $event instanceof Event\FailToExecute)
            || ! $this->telescopeConfig->isEnable('schedule')
        ) {
            return;
        }

        TelescopeContext::getOrSetBatch();

        $output = match (true) {
            $event instanceof Event\AfterExecute => 'success',
            $event instanceof Event\FailToExecute => '[fail]' . (string) $event->getThrowable(),
        };

        Telescope::recordSchedule(IncomingEntry::make([
            'command' => $event->crontab->getName(),
            'description' => $event->crontab->getMemo(),
            'expression' => $event->crontab->getRule(),
            'timezone' => $event->crontab->getTimezone(),
            'user' => '',
            'output' => $output,
        ]));
    }
}
