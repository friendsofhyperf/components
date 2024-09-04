<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Subscriber;

use FriendsOfHyperf\Trigger\Consumer;
use MySQLReplication\Event\DTO\EventDTO;

class SnapshotSubscriber extends AbstractSubscriber
{
    public function __construct(protected Consumer $consumer)
    {
    }

    protected function allEvents(EventDTO $event): void
    {
        if (! $this->consumer->getHealthMonitor()) {
            return;
        }

        $eventInfo = $event->getEventInfo();
        $binLogCurrent = match (true) {
            method_exists($eventInfo, 'getBinLogCurrent') => $eventInfo->getBinLogCurrent(),
            property_exists($eventInfo, 'binLogCurrent') => $eventInfo->binLogCurrent, // @phpstan-ignore-line
            default => null,
        };

        if (! $binLogCurrent) {
            return;
        }

        $this->consumer->getHealthMonitor()->setBinLogCurrent($binLogCurrent);
    }
}
