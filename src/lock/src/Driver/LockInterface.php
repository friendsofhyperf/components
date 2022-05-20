<?php

declare(strict_types=1);
/**
 * This file is part of hyperf-lock.
 *
 * @link     https://github.com/friendsofhyperf/lock
 * @document https://github.com/friendsofhyperf/lock/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Driver;

interface LockInterface
{
    /**
     * Attempt to acquire the lock.
     * @param null|callable $callback
     * @return mixed
     */
    public function get($callback = null);

    /**
     * Attempt to acquire the lock for the given number of seconds.
     * @param int $seconds
     * @param null|callable $callback
     * @return bool
     */
    public function block($seconds, $callback = null);

    /**
     * Release the lock.
     * @return bool
     */
    public function release();

    /**
     * Returns the current owner of the lock.
     * @return string
     */
    public function owner();

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease();
}
