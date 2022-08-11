<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Driver;

use Carbon\Carbon;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\Exception\QueryException;
use Hyperf\DbConnection\Db;

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

        $this->connection = Db::connection($constructor['pool']);
        $this->table = $constructor['table'] ?? 'locks';
    }

    /**
     * Attempt to acquire the lock.
     */
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
                ->where(fn ($query) => $query->where('owner', $this->owner)->orWhere('expiration', '<=', time()))->update([
                    'owner' => $this->owner,
                    'expiration' => $this->expiresAt(),
                ]);

            $acquired = $updated >= 1;
        }

        return $acquired;
    }

    /**
     * Release the lock.
     */
    public function release(): bool
    {
        if ($this->isOwnedByCurrentProcess()) {
            $this->connection->table($this->table)
                ->where('key', $this->name)
                ->where('owner', $this->owner)
                ->delete();

            return true;
        }

        return false;
    }

    /**
     * Releases this lock in disregard of ownership.
     */
    public function forceRelease(): void
    {
        $this->connection->table($this->table)
            ->where('key', $this->name)
            ->delete();
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
        return optional($this->connection->table($this->table)->where('key', $this->name)->first())->owner;
    }
}
