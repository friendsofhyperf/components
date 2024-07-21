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
use Hyperf\Engine\Contract\Socket\SocketFactoryInterface;
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Engine\Socket\SocketFactory;
use Hyperf\Engine\Socket\SocketOption;

class Connection
{
    protected SocketInterface $socket;

    protected bool $closed = false;

    public function __construct(string $host = '', int $port = 6379, float $timeout = 5.0, ?SocketFactoryInterface $factory = null)
    {
        $options = new SocketOption($host, $port, $timeout, [
            'open_eof_check' => true,
            'package_eof' => Constants::EOF,
        ]);
        $factory ??= new SocketFactory();
        $this->socket = $factory->make($options);
    }

    public function send(string $data): bool
    {
        $len = strlen($data);
        $size = $this->socket->sendAll($data);

        if ($size === false) {
            throw new SocketException('Failed to send data to the socket.');
        }

        if ($len !== $size) {
            throw new SocketException('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }

        return true;
    }

    /**
     * @param float $timeout the timeout parameter is used to set the timeout rules for the recv method
     * @see https://wiki.swoole.com/en/#/coroutine_client/init?id=timeout-rules
     * -1: indicates no timeout
     * 0: indicates no change in timeout
     * > 0: represents setting a timeout timer for the corresponding number of seconds, with a maximum precision of 1 millisecond, which is a floating-point number; 0.5 represents 500 milliseconds
     */
    public function recv(float $timeout = -1): string|bool
    {
        return $this->socket->recvPacket($timeout);
    }

    public function close(): void
    {
        if (! $this->closed && ! $this->socket->close()) {
            throw new SocketException('Failed to close the socket.');
        }

        $this->closed = true;
    }
}
