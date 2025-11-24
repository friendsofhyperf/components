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

use Hyperf\Cache\CacheManager;
use Hyperf\Context\ApplicationContext;
use Override;
use Psr\SimpleCache\CacheInterface;

class CacheLock extends AbstractLock
{
    /**
     * The cache store implementation.
     */
    protected CacheInterface $store;

    /**
     * Create a new lock instance.
     */
    public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [], int $heartbeat = 0)
    {
        parent::__construct($name, $seconds, $owner, $heartbeat);

        $container = ApplicationContext::getContainer();
        $cacheManager = $container->get(CacheManager::class);
        $constructor = array_merge(['driver' => 'default'], $constructor);
        $this->store = $cacheManager->getDriver($constructor['driver']);
    }

    /**
     * Attempt to acquire the lock.
     */
    #[Override]
    public function acquire(): bool
    {
        if ($this->store->has($this->name)) {
            return false;
        }

        return $this->store->set($this->name, $this->owner, $this->seconds) && $this->heartbeat();
    }

    /**
     * Release the lock.
     */
    #[Override]
    public function release(): bool
    {
        if ($this->isOwnedByCurrentProcess()) {
            $this->stopHeartbeat();
            return $this->store->delete($this->name);
        }

        return false;
    }

    /**
     * Releases this lock regardless of ownership.
     */
    #[Override]
    public function forceRelease(): void
    {
        $this->stopHeartbeat();
        $this->store->delete($this->name);
    }

    #[Override]
    protected function delayExpiration(): bool
    {
        if ($this->seconds > 0) {
            return $this->store->set($this->name, $this->owner, $this->seconds);
        }
        return true;
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
