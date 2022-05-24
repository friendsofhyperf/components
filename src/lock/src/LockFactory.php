<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock;

use FriendsOfHyperf\Lock\Driver\LockInterface;
use FriendsOfHyperf\Lock\Driver\RedisLock;
use Hyperf\Contract\ConfigInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class LockFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    /**
     * Get a lock instance.
     * @param string $name
     * @param int $seconds
     * @param null|string $owner
     * @param string $driver
     */
    public function make($name, $seconds = 0, $owner = null, $driver = 'default'): LockInterface
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
