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

use Closure;
use FriendsOfHyperf\Support\Bus\PendingAsyncQueueDispatch;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\AsyncQueue\JobInterface;
use Override;

use function FriendsOfHyperf\Support\dispatch;

/**
 * @mixin DriverFactory
 * @property null|string $queue
 * @property null|string $pool
 */
class AsyncQueue extends Facade
{
    public function dispatch(Closure|JobInterface $job): PendingAsyncQueueDispatch
    {
        return dispatch($job);
    }

    /**
     * Push a job to the queue.
     * @return bool
     */
    public static function push(JobInterface $job, int $delay = 0, ?string $pool = null)
    {
        $pool ??= (fn () => $this->queue ?? $this->pool ?? 'default')->call($job);

        return self::get($pool)->push($job, $delay);
    }

    #[Override]
    protected static function getFacadeAccessor()
    {
        return DriverFactory::class;
    }
}
