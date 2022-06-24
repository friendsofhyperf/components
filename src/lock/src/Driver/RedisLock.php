<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Driver;

use Hyperf\Redis\RedisProxy;

class RedisLock extends AbstractLock
{
    /**
     * The Redis factory implementation.
     *
     * @var \Redis
     */
    protected $store;

    /**
     * Create a new lock instance.
     * @param string $name
     * @param int $seconds
     * @param null|string $owner
     */
    public function __construct($name, $seconds, $owner = null, array $constructor = [])
    {
        parent::__construct($name, $seconds, $owner);

        $constructor = array_merge(['pool' => 'default'], $constructor);
        $this->store = make(RedisProxy::class, $constructor);
    }

    /**
     * Attempt to acquire the lock.
     * @return bool
     */
    public function acquire()
    {
        if ($this->seconds > 0) {
            return $this->store->set($this->name, $this->owner, ['NX', 'EX' => $this->seconds]) == true;
        }

        return $this->store->setNX($this->name, $this->owner) === true;
    }

    /**
     * Release the lock.
     * @return bool
     */
    public function release()
    {
        return (bool) $this->store->eval(LuaScripts::releaseLock(), [$this->name, $this->owner], 1);
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease()
    {
        $this->store->del($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     * @return string
     */
    protected function getCurrentOwner()
    {
        return $this->store->get($this->name);
    }
}
