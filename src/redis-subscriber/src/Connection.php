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
use Hyperf\Engine\Contract\SocketInterface;
use Hyperf\Engine\Socket;
use Hyperf\Engine\Socket\SocketFactory;
use Hyperf\Engine\Socket\SocketOption;

class Connection
{
    /**
     * @var Socket
     */
    protected SocketInterface $client;

    protected bool $closed = false;

    public function __construct(
        public string $host = '',
        public int $port = 6379,
        public float $timeout = 5.0
    ) {
        $options = new SocketOption($this->host, $this->port, $this->timeout, [
            'open_eof_check' => true,
            'package_eof' => Constants::EOF,
        ]);
        /** @var Socket $client fixed for phpstan */
        $client = (new SocketFactory())->make($options);
        $this->client = $client;
    }

    public function send(string $data): bool
    {
        $len = strlen($data);
        $size = $this->client->send($data);

        if ($size === false) {
            throw new SocketException($this->client->errMsg, $this->client->errCode);
        }
        if ($len !== $size) {
            throw new SocketException('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
        return true;
    }

    /**
     * Recv.
     * @return string|bool
     */
    public function recv()
    {
        return $this->client->recv(timeout: -1);
    }

    public function close(): void
    {
        if (! $this->closed && ! $this->client->close()) {
            $errMsg = $this->client->errMsg;
            $errCode = $this->client->errCode;
            if ($errMsg == '' && $errCode == 0) {
                return;
            }
            throw new SocketException($errMsg, $errCode);
        }
        $this->closed = true;
    }
}
