<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Lock;

use FriendsOfHyperf\Lock\Driver\LockInterface;
use FriendsOfHyperf\Lock\Driver\RedisLock;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;

use function Hyperf\Support\make;

class LockFactory
{
    public function __construct(private ConfigInterface $config)
    {
    }

    /**
     * Get a lock instance.
     */
    public function make(string $name, int $seconds = 0, ?string $owner = null, string $driver = 'default'): LockInterface
    {
        $driver = $driver ?: 'default';

        if (! $this->config->has("lock.{$driver}")) {
            throw new InvalidArgumentException(sprintf('The lock config %s is invalid.', $driver));
        }

        $driverClass = $this->config->get("lock.{$driver}.driver", RedisLock::class);
        $constructor = $this->config->get("lock.{$driver}.constructor", ['config' => []]);

        return make($driverClass, [
            'name' => $name,
            'seconds' => $seconds,
            'owner' => $owner,
            'constructor' => $constructor,
        ]);
    }
}
