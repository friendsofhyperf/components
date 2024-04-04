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

use Swoole\Coroutine\Client;
use Swoole\Exception;

class Connection
{
    public const EOF = "\r\n";

    public string $host = '';

    public int $port = 6379;

    public float $timeout = 0.0;

    protected Client $client;

    protected bool $closed = false;

    public function __construct(string $host, int $port, float $timeout = 5.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
        $client = new Client(SWOOLE_SOCK_TCP);
        $client->set([
            'open_eof_check' => true,
            'package_eof' => static::EOF,
        ]);
        if (! $client->connect($host, $port, $timeout)) {
            throw new Exception(sprintf('Redis connect failed (host: %s, port: %s) %d %s', $host, $port, $client->errCode, $client->errMsg));
        }
        $this->client = $client;
    }

    public function send(string $data): bool
    {
        $len = strlen($data);
        $size = $this->client->send($data);
        if ($size === false) {
            throw new Exception($this->client->errMsg, $this->client->errCode);
        }
        if ($len !== $size) {
            throw new Exception('The sending data is incomplete, it may be that the socket has been closed by the peer.');
        }
        return true;
    }

    /**
     * Recv.
     * @return string|bool
     */
    public function recv()
    {
        return $this->client->recv(-1);
    }

    public function close(): void
    {
        if (! $this->closed && ! $this->client->close()) {
            $errMsg = $this->client->errMsg;
            $errCode = $this->client->errCode;
            if ($errMsg == '' && $errCode == 0) {
                return;
            }
            throw new Exception($errMsg, $errCode);
        }
        $this->closed = true;
    }
}
