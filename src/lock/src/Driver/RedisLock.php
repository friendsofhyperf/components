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
        $result = false;

        if ($this->seconds > 0) {
            $result = $this->store->set($this->name, $this->owner, ['NX', 'EX' => $this->seconds]) == true;
        } else {
            $result = $this->store->setNX($this->name, $this->owner) === true;
        }

        if ($result) {
            $this->acquiredAt = microtime(true);
        }

        return $result;
    }

    /**
     * Release the lock.
     */
    #[Override]
    public function release(): bool
    {
        $result = (bool) $this->store->eval(LuaScripts::releaseLock(), [$this->name, $this->owner], 1);

        if ($result) {
            $this->acquiredAt = null;
        }

        return $result;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    #[Override]
    public function forceRelease(): void
    {
        $this->store->del($this->name);
        $this->acquiredAt = null;
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

        $result = (bool) $this->store->eval(LuaScripts::refreshLock(), [$this->name, $this->owner, $ttl], 1);

        if ($result) {
            $this->seconds = $ttl;
            $this->acquiredAt = microtime(true);
        }

        return $result;
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
