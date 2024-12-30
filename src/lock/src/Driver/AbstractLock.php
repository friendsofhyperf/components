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
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Engine\Coroutine;
use Hyperf\Stringable\Str;
use Hyperf\Support\Traits\InteractsWithTime;
use Override;

use Throwable;
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

    protected bool $isRun = true;

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
     * Set the lock.
     */
    abstract protected function set(): bool;

    /**
     * Release the lock.
     */
    abstract public function release(): bool;

    /**
     * Attempt to acquire the lock.
     * {@inheritdoc}
     */
    #[Override]
    public function get(?callable $callback = null, int $heartbeat = 0)
    {
        $result = $this->acquire();

        if ($result && $heartbeat > 0){
            $this->loop($heartbeat);
        }

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
    public function block(int $seconds, ?callable $callback = null, int $heartbeat = 0)
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

        if ($heartbeat > 0){
            $this->loop($heartbeat);
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
     * @param string|null $owner
     */
    public function isOwnedBy($owner): bool
    {
        return $this->getCurrentOwner() === $owner;
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

    protected function loop(int $heartbeat = 5): void
    {
         Coroutine::create(
            function () use ($heartbeat) {
                while ($this->isRun) {
                    sleep($heartbeat);
                    if (!$this->isRun){
                        return;
                    }
                    try {
                        $this->set();
                    } catch (Throwable $throwable){
                        ApplicationContext::getContainer()->get(StdoutLoggerInterface::class)?->error((string) $throwable);
                        sleep(5);
                    }
                }
            }
        );
    }

}
