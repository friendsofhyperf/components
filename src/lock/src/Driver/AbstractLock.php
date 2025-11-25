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
use Hyperf\Stringable\Str;
use Hyperf\Support\Traits\InteractsWithTime;
use Override;

use function Hyperf\Support\now;

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
     * The timestamp when the lock was acquired.
     */
    protected ?float $acquiredAt = null;

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
     * {@inheritdoc}
     */
    #[Override]
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
     * {@inheritdoc}
     */
    #[Override]
    public function block(int $seconds, ?callable $callback = null)
    {
        $starting = ((int) now()->format('Uu')) / 1000;
        $milliseconds = $seconds * 1000;

        while (! $this->acquire()) {
            $now = ((int) now()->format('Uu')) / 1000;

            if (($now + $this->sleepMilliseconds - $milliseconds) >= $starting) {
                throw new LockTimeoutException();
            }

            usleep($this->sleepMilliseconds * 1000);
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
     * Determine whether this lock is owned by the given identifier.
     *
     * @param null|string $owner
     */
    public function isOwnedBy($owner): bool
    {
        return $this->getCurrentOwner() === $owner;
    }

    /**
     * Refresh the lock expiration time.
     * {@inheritdoc}
     */
    #[Override]
    abstract public function refresh(?int $ttl = null): bool;

    /**
     * Check if the lock has expired.
     * {@inheritdoc}
     */
    #[Override]
    public function isExpired(): bool
    {
        if ($this->seconds <= 0) {
            return false;
        }

        if ($this->acquiredAt === null) {
            return true;
        }

        return microtime(true) >= ($this->acquiredAt + $this->seconds);
    }

    /**
     * Get the remaining lifetime of the lock in seconds.
     * {@inheritdoc}
     */
    #[Override]
    public function getRemainingLifetime(): ?float
    {
        if ($this->seconds <= 0) {
            return null;
        }

        if ($this->acquiredAt === null) {
            return null;
        }

        $remaining = ($this->acquiredAt + $this->seconds) - microtime(true);

        return $remaining > 0 ? $remaining : 0.0;
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
        return $this->isOwnedBy($this->owner);
    }
}
