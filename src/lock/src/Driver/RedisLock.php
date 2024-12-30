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

use Hyperf\Redis\RedisProxy;
use Override;

use function Hyperf\Support\make;

class RedisLock extends AbstractLock
{
    /**
     * The Redis factory implementation.
     *
     * @var \Hyperf\Redis\Redis
     */
    protected $store;

    /**
     * Create a new lock instance.
     */
    public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
    {
        parent::__construct($name, $seconds, $owner);

        $constructor = array_merge(['pool' => 'default', 'prefix' => ''], $constructor);
        if ($constructor['prefix']) {
            $this->name = ((string) $constructor['prefix']) . $this->name;
        }
        $this->store = make(RedisProxy::class, $constructor);
    }

    /**
     * Attempt to acquire the lock.
     */
    #[Override]
    public function acquire(): bool
    {
        if ($this->seconds > 0) {
            return $this->store->set($this->name, $this->owner, ['NX', 'EX' => $this->seconds]) == true;
        }

        return $this->store->setNX($this->name, $this->owner) === true;
    }

    /**
     * Set the lock.
     */
    #[Override]
    protected function set(): bool
    {
        if ($this->seconds > 0) {
            return $this->store->set($this->name, $this->owner, ['EX' => $this->seconds]) == true;
        }
        return $this->store->setNX($this->name, $this->owner) === true;
    }


    /**
     * Release the lock.
     */
    #[Override]
    public function release(): bool
    {
        $this->isRun = false;
        return (bool) $this->store->eval(LuaScripts::releaseLock(), [$this->name, $this->owner], 1);
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    #[Override]
    public function forceRelease(): void
    {
        $this->isRun = false;
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
