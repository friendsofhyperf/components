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

interface LockInterface
{
    /**
     * Attempt to acquire the lock.
     * @template T
     * @param null|(callable(): T) $callback
     * @return ($callback is null ? bool : T)
     */
    public function get(?callable $callback = null);

    /**
     * Attempt to acquire the lock for the given number of seconds.
     *
     * @template T
     * @param null|(callable(): T) $callback
     * @return ($callback is null ? bool : T)
     * @throws LockTimeoutException
     */
    public function block(int $seconds, ?callable $callback = null);

    /**
     * Release the lock.
     */
    public function release(): bool;

    /**
     * Returns the current owner of the lock.
     */
    public function owner(): string;

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease(): void;
}
