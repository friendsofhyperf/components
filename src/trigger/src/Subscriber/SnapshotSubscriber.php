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
        if (! $this->consumer->healthMonitor) {
            return;
        }

        $eventInfo = $event->getEventInfo();
        $binLogCurrent = match (true) {
            method_exists($eventInfo, 'getBinLogCurrent') => $eventInfo->getBinLogCurrent(), // v7.x, @deprecated since v3.1, will be removed in v3.2
            property_exists($eventInfo, 'binLogCurrent') => $eventInfo->binLogCurrent, // @phpstan-ignore property.private
            default => null,
        };

        if (! $binLogCurrent) {
            return;
        }

        $this->consumer->healthMonitor->setBinLogCurrent($binLogCurrent);
    }
}
