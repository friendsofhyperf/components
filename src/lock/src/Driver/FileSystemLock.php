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

        return $this->store->set($this->name, $this->owner, $this->seconds) == true;
    }

    /**
     * Set the lock.
     */
    #[Override]
    protected function set(): bool
    {
        return $this->store->set($this->name, $this->owner, $this->seconds) == true;
    }

    /**
     * Release the lock.
     */
    #[Override]
    public function release(): bool
    {
        $this->isRun = false;
        if ($this->isOwnedByCurrentProcess()) {
            return $this->store->delete($this->name);
        }

        return false;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    #[Override]
    public function forceRelease(): void
    {
        $this->isRun = false;
        $this->store->delete($this->name);
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
