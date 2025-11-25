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

use Hyperf\Coordinator\Timer;
use Hyperf\Engine\Channel;
use Override;
use Throwable;
use WeakMap;

class CoroutineLock extends AbstractLock
{
    /**
     * Mapping of lock names to their corresponding channels.
     *
     * @var array<string, Channel>
     */
    protected static array $channels = [];

    /**
     * Mapping of channels to their current owners (used for ownership verification).
     *
     * @var null|WeakMap<Channel, string>
     */
    protected static ?WeakMap $owners = null;

    /**
     * Timer instance for scheduling lock expiration.
     */
    protected static ?Timer $timer = null;

    /**
     * Mapping of channels to their timer IDs (for clearing expiration timers).
     *
     * @var null|WeakMap<Channel, int>
     */
    protected static ?WeakMap $timerIds = null;

    /**
     * Mapping of channels to their acquisition timestamps (for tracking expiration).
     *
     * @var null|WeakMap<Channel, float>
     */
    protected static ?WeakMap $acquiredTimes = null;

    /**
     * Mapping of channels to their TTL values (for tracking remaining lifetime).
     *
     * @var null|WeakMap<Channel, int>
     */
    protected static ?WeakMap $ttls = null;

    /**
     * Create a new lock instance.
     */
    public function __construct(
        string $name,
        int $seconds,
        ?string $owner = null,
        array $constructor = []
    ) {
        $constructor = array_merge(['prefix' => ''], $constructor);
        $name = $constructor['prefix'] . $name;

        parent::__construct($name, $seconds, $owner);

        self::$owners ??= new WeakMap();
        self::$acquiredTimes ??= new WeakMap();
        self::$ttls ??= new WeakMap();
        self::$timer ??= new Timer();
        self::$timerIds ??= new WeakMap();
    }

    /**
     * Attempt to acquire the lock.
     */
    #[Override]
    public function acquire(): bool
    {
        try {
            $chan = self::$channels[$this->name] ??= new Channel(1);

            if (! $chan->push(1, 0.01)) {
                return false;
            }

            self::$owners[$chan] = $this->owner;
            $this->acquiredAt = microtime(true);
            self::$acquiredTimes[$chan] = $this->acquiredAt;
            self::$ttls[$chan] = $this->seconds;

            if ($timeId = self::$timerIds[$chan] ?? null) {
                self::$timer?->clear((int) $timeId);
            }

            if ($this->seconds > 0) {
                $timeId = self::$timer?->after($this->seconds, fn () => $this->forceRelease());
                $timeId && self::$timerIds[$chan] = $timeId;
            }
        } catch (Throwable) {
            return false;
        }

        return true;
    }

    /**
     * Release the lock.
     */
    #[Override]
    public function release(): bool
    {
        if ($this->isOwnedByCurrentProcess()) {
            $result = (self::$channels[$this->name] ?? null)?->pop(0.01) ? true : false;

            if ($result) {
                $this->acquiredAt = null;
            }

            return $result;
        }

        return false;
    }

    /**
     * Releases this lock regardless of ownership.
     */
    #[Override]
    public function forceRelease(): void
    {
        if (! $chan = self::$channels[$this->name] ?? null) {
            return;
        }

        self::$channels[$this->name] = null;
        $this->acquiredAt = null;

        $chan->close();
    }

    /**
     * Refresh the lock expiration time.
     */
    #[Override]
    public function refresh(?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->seconds;

        if ($ttl <= 0) {
            return false;
        }

        if (! $this->isOwnedByCurrentProcess()) {
            return false;
        }

        if (! $chan = self::$channels[$this->name] ?? null) {
            return false;
        }

        // Clear existing timer
        if ($timeId = self::$timerIds[$chan] ?? null) {
            self::$timer?->clear((int) $timeId);
        }

        // Update TTL and acquired time
        $this->seconds = $ttl;
        $this->acquiredAt = microtime(true);
        self::$acquiredTimes[$chan] = $this->acquiredAt;
        self::$ttls[$chan] = $ttl;

        // Set new timer
        $timeId = self::$timer?->after($ttl, fn () => $this->forceRelease());
        $timeId && self::$timerIds[$chan] = $timeId;

        return true;
    }

    /**
     * Check if the lock has expired.
     */
    #[Override]
    public function isExpired(): bool
    {
        if ($this->seconds <= 0) {
            return false;
        }

        if (! $chan = self::$channels[$this->name] ?? null) {
            return true;
        }

        $acquiredAt = self::$acquiredTimes[$chan] ?? null;
        $ttl = self::$ttls[$chan] ?? $this->seconds;

        if ($acquiredAt === null) {
            return true;
        }

        return microtime(true) >= ($acquiredAt + $ttl);
    }

    /**
     * Get the remaining lifetime of the lock in seconds.
     */
    #[Override]
    public function getRemainingLifetime(): ?float
    {
        if ($this->seconds <= 0) {
            return null;
        }

        if (! $chan = self::$channels[$this->name] ?? null) {
            return null;
        }

        $acquiredAt = self::$acquiredTimes[$chan] ?? null;
        $ttl = self::$ttls[$chan] ?? $this->seconds;

        if ($acquiredAt === null) {
            return null;
        }

        $remaining = ($acquiredAt + $ttl) - microtime(true);

        return $remaining > 0 ? $remaining : 0.0;
    }

    /**
     * Returns the owner value written into the driver for this lock.
     * @return string
     */
    protected function getCurrentOwner()
    {
        if (! $chan = self::$channels[$this->name] ?? null) {
            return '';
        }

        return self::$owners[$chan] ?? '';
    }
}
