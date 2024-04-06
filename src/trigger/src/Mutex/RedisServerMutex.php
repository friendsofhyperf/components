<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Mutex;

use FriendsOfHyperf\Trigger\Traits\Logger;
use FriendsOfHyperf\Trigger\Util;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Redis\Redis;
use RedisException;
use Throwable;

class RedisServerMutex implements ServerMutexInterface
{
    use Logger;

    protected int $expires = 60;

    protected int $keepaliveInterval = 10;

    protected Timer $timer;

    protected bool $released = false;

    protected int $retryInterval = 10;

    protected string $connection = 'default';

    public function __construct(
        protected Redis $redis,
        protected ?string $name = null,
        protected ?string $owner = null,
        array $options = [],
        protected ?StdoutLoggerInterface $logger = null
    ) {
        $this->expires = (int) ($options['expires'] ?? 60);
        $this->keepaliveInterval = (int) ($options['keepalive_interval'] ?? 10);
        $this->name = $name ?? sprintf('trigger:server:%s', $this->connection);
        $this->owner = $owner ?? Util::getInternalIp();
        $this->connection = $options['connection'];
        $this->timer = new Timer($logger);
        $this->retryInterval = (int) ($options['retry_interval'] ?? 10);
    }

    public function attempt(?callable $callback = null): void
    {
        // Waiting for the server mutex.
        $this->timer->tick($this->retryInterval, function () {
            if (
                $this->redis->set($this->name, $this->owner, ['NX', 'EX' => $this->expires])
                || $this->redis->get($this->name) == $this->owner
            ) {
                $this->debug('Got server mutex.');
                CoordinatorManager::until($this->getIdentifier())->resume();

                return Timer::STOP;
            }

            $this->debug('Waiting server mutex.');
        });

        // Waiting for the server mutex.
        CoordinatorManager::until($this->getIdentifier())->yield();

        $this->debug('Server mutex keepalive booted.');

        $this->timer->tick($this->keepaliveInterval, function () {
            if ($this->released) {
                $this->debug('Server mutex keepalive stopped.');

                return Timer::STOP;
            }

            $this->redis->setNx($this->name, $this->owner);
            $this->redis->expire($this->name, $this->expires);
            $ttl = $this->redis->ttl($this->name);

            $this->debug('Server mutex keepalive executed', ['ttl' => $ttl]);
        });

        // Execute the callback.
        if ($callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                $this->error((string) $e);
            }
        }
    }

    /**
     * Release the server mutex.
     * @throws RedisException
     */
    public function release(bool $force = false): void
    {
        if ($force || $this->redis->get($this->name) == $this->owner) {
            $this->redis->del($this->name);
            $this->released = true;
        }
    }

    protected function getIdentifier(): string
    {
        return sprintf('%s_%s', $this->connection, __CLASS__);
    }
}
