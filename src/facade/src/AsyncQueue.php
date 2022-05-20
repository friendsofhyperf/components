<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
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
     * @param int $delay
     * @param string $queue
     * @throws InvalidDriverException
     * @return bool
     */
    public static function push(JobInterface $job, $delay = 0, $queue = 'default')
    {
        return self::get($queue)->push($job, $delay);
    }

    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
