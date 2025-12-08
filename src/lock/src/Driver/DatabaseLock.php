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

use Carbon\Carbon;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\QueryException;
use Hyperf\DbConnection\Db;
use Override;

class DatabaseLock extends AbstractLock
{
    protected ConnectionInterface $connection;

    protected string $table;

    /**
     * Create a new lock instance.
     */
    public function __construct(string $name, int $seconds, ?string $owner = null, array $constructor = [])
    {
        parent::__construct($name, $seconds, $owner);

        $constructor = array_merge(['pool' => 'default', 'table' => 'locks', 'prefix' => ''], $constructor);
        if ($constructor['prefix']) {
            $this->name = ((string) $constructor['prefix']) . $this->name;
        }
        $this->connection = Db::connection($constructor['pool']);
        $this->table = $constructor['table'];
    }

    /**
     * Attempt to acquire the lock.
     */
    #[Override]
    public function acquire(): bool
    {
        $acquired = false;

        try {
            $this->connection->table($this->table)->insert([
                'key' => $this->name,
                'owner' => $this->owner,
                'expiration' => $this->expiresAt(),
            ]);

            $acquired = true;
        } catch (QueryException) {
            $updated = $this->connection->table($this->table)
                ->where('key', $this->name)
                ->where(fn ($query) => $query->where('owner', $this->owner)->orWhere('expiration', '<=', time()))
                ->update([
                    'owner' => $this->owner,
                    'expiration' => $this->expiresAt(),
                ]);

            $acquired = $updated >= 1;
        }

        if ($acquired) {
            $this->acquiredAt = microtime(true);
        }

        return $acquired;
    }

    /**
     * Release the lock.
     */
    #[Override]
    public function release(): bool
    {
        if ($this->isOwnedByCurrentProcess()) {
            $this->connection->table($this->table)
                ->where('key', $this->name)
                ->where('owner', $this->owner)
                ->delete();

            $this->acquiredAt = null;

            return true;
        }

        return false;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    #[Override]
    public function forceRelease(): void
    {
        $this->connection->table($this->table)
            ->where('key', $this->name)
            ->delete();
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

        $updated = $this->connection->table($this->table)
            ->where('key', $this->name)
            ->where('owner', $this->owner)
            ->update([
                'expiration' => time() + $ttl,
            ]);

        if ($updated >= 1) {
            $this->seconds = $ttl;
            $this->acquiredAt = microtime(true);
            return true;
        }

        return false;
    }

    /**
     * Get the UNIX timestamp indicating when the lock should expire.
     */
    protected function expiresAt(): int
    {
        return $this->seconds > 0 ? time() + $this->seconds : Carbon::now()->addDays(1)->getTimestamp();
    }

    /**
     * Returns the owner value written into the driver for this lock.
     * @return string
     */
    protected function getCurrentOwner()
    {
        return $this->connection->table($this->table)->where('key', $this->name)->first()?->owner; // @phpstan-ignore-line
    }
}
