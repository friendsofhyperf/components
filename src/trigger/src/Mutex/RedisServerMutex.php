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

use FriendsOfHyperf\Trigger\Util;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coordinator\Timer;
use Hyperf\Redis\Redis;
use Psr\Log\LoggerInterface;
use RedisException;
use Throwable;

class RedisServerMutex implements ServerMutexInterface
{
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
        protected ?LoggerInterface $logger = null
    ) {
        $this->expires = (int) ($options['expires'] ?? 60);
        $this->keepaliveInterval = (int) ($options['keepalive_interval'] ?? 10);
        $this->name = $name ?? sprintf('trigger:server:%s', $this->connection);
        $this->owner = $owner ?? Util::getInternalIp();
        if (isset($options['connection'])) {
            $this->connection = $options['connection'];
        }
        $this->timer = new Timer($this->logger);
        $this->retryInterval = (int) ($options['retry_interval'] ?? 10);
    }

    public function attempt(?callable $callback = null): void
    {
        $context = ['connection' => $this->connection];

        // Waiting for the server mutex.
        $this->timer->tick($this->retryInterval, function () use ($context) {
            if (
                $this->redis->set($this->name, $this->owner, ['NX', 'EX' => $this->expires])
                || $this->redis->get($this->name) == $this->owner
            ) {
                $this->logger?->debug('Got server mutex.');
                CoordinatorManager::until($this->getIdentifier())->resume();

                return Timer::STOP;
            }

            $this->logger?->debug('Waiting server mutex.', $context);
        });

        // Waiting for the server mutex.
        CoordinatorManager::until($this->getIdentifier())->yield();

        $this->logger?->debug('Server mutex keepalive booted.', $context);

        $this->timer->tick($this->keepaliveInterval, function () use ($context) {
            if ($this->released) {
                $this->logger?->debug('Server mutex keepalive stopped.', $context);

                return Timer::STOP;
            }

            $this->redis->setNx($this->name, $this->owner);
            $this->redis->expire($this->name, $this->expires);
            $ttl = $this->redis->ttl($this->name);

            $this->logger?->debug('Server mutex keepalive executed', $context + ['ttl' => $ttl]);
        });

        // Execute the callback.
        if ($callback) {
            try {
                $callback();
            } catch (Throwable $e) {
                $this->logger?->error((string) $e, $context);
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
