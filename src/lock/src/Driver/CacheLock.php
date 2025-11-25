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
    public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
    {
        parent::__construct($name, $seconds, $owner);

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

        $result = $this->store->set($this->name, $this->owner, $this->seconds);

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
        if ($this->isOwnedByCurrentProcess()) {
            $result = $this->store->delete($this->name);

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
        $this->store->delete($this->name);
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

        if (! $this->isOwnedByCurrentProcess()) {
            return false;
        }

        $result = $this->store->set($this->name, $this->owner, $ttl);

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
