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

use Hyperf\AsyncQueue\Driver\DriverFactory as Accessor;
use Hyperf\AsyncQueue\Exception\InvalidDriverException;
use Hyperf\AsyncQueue\JobInterface;

/**
 * @mixin Accessor
 */
class AsyncQueue extends Facade
{
    /**
     * Push a job to the queue.
     * @return bool
     * @throws InvalidDriverException
     */
    public static function push(JobInterface $job, int $delay = 0, string $queue = 'default')
    {
        return self::get($queue)->push($job, $delay);
    }

    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
