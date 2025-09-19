<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Override;

/**
 * @mixin DriverFactory
 * @property null|string $queue
 */
class AsyncQueue extends Facade
{
    /**
     * Push a job to the queue.
     * @return bool
     */
    public static function push(JobInterface $job, int $delay = 0, ?string $queue = null)
    {
        $queue = (fn ($queue) => $this->queue ?? $queue)->call($job, $queue);

        return self::get($queue)->push($job, $delay);
    }

    #[Override]
    protected static function getFacadeAccessor()
    {
        return DriverFactory::class;
    }
}
