<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Redis\Subscriber;

use FriendsOfHyperf\Redis\Subscriber\Exception\SocketException;
use FriendsOfHyperf\Redis\Subscriber\Exception\SubscribeException;
use FriendsOfHyperf\Redis\Subscriber\Exception\UnsubscribeException;
use Hyperf\Contract\StdoutLoggerInterface;
use Throwable;

class Subscriber
{
    public bool $closed = false;

    protected CommandInvoker $commandInvoker;

    public function __construct(
        public string $host,
        public int $port = 6379,
        public string $password = '',
        public float $timeout = 5.0,
        public string $prefix = '',
        protected ?StdoutLoggerInterface $logger = null,
    ) {
        $this->connect();
    }

    /**
     * @throws SocketException
     * @throws Throwable
     * @throws SubscribeException
     */
    public function subscribe(string ...$channels): void
    {
        $channels = array_map(fn ($channel) => $this->prefix . $channel, $channels);
        $result = $this->commandInvoker->invoke(['subscribe', ...$channels], count($channels));

        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new SubscribeException('Subscribe failed');
            }
        }
    }

    /**
     * @throws SocketException
     * @throws Throwable
     * @throws UnsubscribeException
     */
    public function unsubscribe(string ...$channels): void
    {
        $channels = array_map(fn ($channel) => $this->prefix . $channel, $channels);
        $result = $this->commandInvoker->invoke(['unsubscribe', ...$channels], count($channels));

        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new UnsubscribeException('Unsubscribe failed');
            }
        }
    }

    /**
     * @throws SocketException
     * @throws Throwable
     * @throws SubscribeException
     */
    public function psubscribe(string ...$channels): void
    {
        $channels = array_map(fn ($channel) => $this->prefix . $channel, $channels);
        $result = $this->commandInvoker->invoke(['psubscribe', ...$channels], count($channels));

        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new SubscribeException('Psubscribe failed');
            }
        }
    }

    /**
     * @throws SocketException
     * @throws Throwable
     * @throws UnsubscribeException
     */
    public function punsubscribe(string ...$channels): void
    {
        $channels = array_map(fn ($channel) => $this->prefix . $channel, $channels);
        $result = $this->commandInvoker->invoke(['punsubscribe', ...$channels], count($channels));

        foreach ($result as $value) {
            if ($value === false) {
                $this->commandInvoker->interrupt();
                throw new UnsubscribeException('Punsubscribe failed');
            }
        }
    }

    /**
     * @return \Hyperf\Engine\Channel
     */
    public function channel()
    {
        return $this->commandInvoker->channel();
    }

    /**
     * @throws SocketException
     */
    public function close(): void
    {
        $this->closed = true;
        $this->commandInvoker->interrupt();
    }

    /**
     * @throws SocketException
     */
    public function ping(float $timeout = 1): string|bool
    {
        return $this->commandInvoker->ping($timeout);
    }

    /**
     * @throws SocketException
     */
    protected function connect(): void
    {
        $connection = new Connection($this->host, $this->port, $this->timeout);
        $this->commandInvoker = new CommandInvoker($connection, $this->logger);

        if ($this->password != '') {
            $this->commandInvoker->invoke(['auth', $this->password], 1);
        }
    }
}
