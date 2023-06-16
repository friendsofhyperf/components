<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Traits;

use Carbon\Carbon;
use DateInterval;
use DateTimeInterface;

trait InteractsWithTime
{
    /**
     * Get the number of seconds until the given DateTime.
     *
     * @param DateInterval|DateTimeInterface|int $delay
     */
    protected function secondsUntil($delay): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? max(0, $delay->getTimestamp() - $this->currentTime())
                            : (int) $delay;
    }

    /**
     * Get the "available at" UNIX timestamp.
     *
     * @param DateInterval|DateTimeInterface|int $delay
     */
    protected function availableAt($delay = 0): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
                            ? $delay->getTimestamp()
                            : Carbon::now()->addSeconds($delay)->getTimestamp();
    }

    /**
     * If the given value is an interval, convert it to a DateTime instance.
     *
     * @param DateInterval|DateTimeInterface|int $delay
     * @return DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }

    /**
     * Get the current system time as a UNIX timestamp.
     */
    protected function currentTime(): int
    {
        return Carbon::now()->getTimestamp();
    }
}
