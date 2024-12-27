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
     * @var array<string, Channel>
     */
    protected static array $channels = [];

    /**
     * @var WeakMap<Channel, string>|null
     */
    protected static ?WeakMap $owners = null;

    protected static ?Timer $timer = null;

    /**
     * @var WeakMap<Channel, int>|null
     */
    protected static ?WeakMap $timers = null;

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
        self::$timers ??= new WeakMap();
        self::$timer ??= new Timer();
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

            if ($timeId = self::$timers[$chan] ?? null) {
                self::$timer?->clear((int) $timeId);
            }

            if ($this->seconds > 0) {
                $timeId = self::$timer?->after($this->seconds * 1000, fn () => $this->forceRelease());
                $timeId && self::$timers[$chan] = $timeId;
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
            return (self::$channels[$this->name] ?? null)?->pop(0.01) ? true : false;
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

        $chan->close();
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
