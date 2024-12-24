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

    protected static ?WeakMap $owners = null;

    /**
     * Create a new lock instance.
     */
    public function __construct(
        string $name,
        int $seconds,
        ?string $owner = null,
        array $constructor = []
    ) {
        $constructor = array_merge([
            'prefix' => '',
        ], $constructor);

        $name = $constructor['prefix'] . $name;

        parent::__construct($name, $seconds, $owner);
    }

    /**
     * Attempt to acquire the lock.
     */
    #[Override]
    public function acquire(): bool
    {
        try {
            $chan = self::$channels[$this->name] ??= new Channel(1);

            // Wait for the specified number of seconds to acquire the lock.
            $chan->push(1, $this->seconds * 1000);

            if ($chan->isTimeout() || $chan->isClosing()) {
                return false;
            }

            if (is_null(self::$owners)) {
                self::$owners = new WeakMap();
            }

            // Save the owner so we can release the lock later.
            self::$owners[$chan] = $this->owner;
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
            $this->forceRelease();
            return true;
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
