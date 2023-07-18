<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Driver;

use FriendsOfHyperf\Lock\Exception\LockTimeoutException;
use FriendsOfHyperf\Lock\Traits\InteractsWithTime;
use Hyperf\Stringable\Str;

abstract class AbstractLock implements LockInterface
{
    use InteractsWithTime;

    /**
     * The scope identifier of this lock.
     */
    protected string $owner;

    /**
     * The number of milliseconds to wait before re-attempting to acquire a lock while blocking.
     */
    protected int $sleepMilliseconds = 250;

    /**
     * Create a new lock instance.
     */
    public function __construct(protected string $name, protected int $seconds, ?string $owner = null)
    {
        $this->owner = $owner ?? Str::random();
    }

    /**
     * Attempt to acquire the lock.
     */
    abstract public function acquire(): bool;

    /**
     * Release the lock.
     */
    abstract public function release(): bool;

    /**
     * Attempt to acquire the lock.
     * @return mixed
     */
    public function get(?callable $callback = null)
    {
        $result = $this->acquire();

        if ($result && is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return $result;
    }

    /**
     * Attempt to acquire the lock for the given number of seconds.
     * @return mixed
     * @throws LockTimeoutException
     */
    public function block(int $seconds, ?callable $callback = null)
    {
        $starting = $this->currentTime();

        while (! $this->acquire()) {
            usleep($this->sleepMilliseconds * 1000);

            if ($this->currentTime() - $seconds >= $starting) {
                throw new LockTimeoutException();
            }
        }

        if (is_callable($callback)) {
            try {
                return $callback();
            } finally {
                $this->release();
            }
        }

        return true;
    }

    /**
     * Returns the current owner of the lock.
     */
    public function owner(): string
    {
        return $this->owner;
    }

    /**
     * Specify the number of milliseconds to sleep in between blocked lock aquisition attempts.
     * @param int $milliseconds
     * @return $this
     */
    public function betweenBlockedAttemptsSleepFor($milliseconds): self
    {
        $this->sleepMilliseconds = $milliseconds;

        return $this;
    }

    /**
     * Returns the owner value written into the driver for this lock.
     */
    abstract protected function getCurrentOwner();

    /**
     * Determines whether this lock is allowed to release the lock in the driver.
     */
    protected function isOwnedByCurrentProcess(): bool
    {
        return $this->getCurrentOwner() === $this->owner;
    }
}
