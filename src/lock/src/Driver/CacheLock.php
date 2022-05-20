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

use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

class CacheLock extends AbstractLock
{
    /**
     * The cache store implementation.
     *
     * @var CacheInterface
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

        $this->store = ApplicationContext::getContainer()->get(CacheInterface::class);
    }

    /**
     * Attempt to acquire the lock.
     * @return bool
     */
    public function acquire()
    {
        if ($this->store->has($this->name)) {
            return false;
        }

        return $this->store->set($this->name, $this->owner, $this->seconds);
    }

    /**
     * Release the lock.
     * @return bool
     */
    public function release()
    {
        if ($this->isOwnedByCurrentProcess()) {
            return $this->store->delete($this->name);
        }

        return false;
    }

    /**
     * Releases this lock regardless of ownership.
     */
    public function forceRelease()
    {
        $this->store->delete($this->name);
    }

    /**
     * Returns the owner value written into the driver for this lock.
     * @return mixed
     */
    protected function getCurrentOwner()
    {
        return $this->store->get($this->name);
    }
}
