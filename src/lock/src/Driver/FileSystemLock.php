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

use Hyperf\Cache\Driver\FileSystemDriver;
use Override;

use function Hyperf\Support\make;

class FileSystemLock extends AbstractLock
{
    /**
     * The FileSystem factory implementation.
     */
    protected FileSystemDriver $store;

    /**
     * Create a new lock instance.
     */
    public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
    {
        parent::__construct($name, $seconds, $owner);

        $constructor = array_merge(['config' => ['prefix' => 'lock:']], $constructor);
        $this->store = make(FileSystemDriver::class, $constructor);
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

        $result = $this->store->set($this->name, $this->owner, $this->seconds) == true;

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
     * Releases this lock in disregard of ownership.
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

        $result = $this->store->set($this->name, $this->owner, $ttl) == true;

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
