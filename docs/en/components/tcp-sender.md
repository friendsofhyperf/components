# Tcp Sender

## Introduction

`friendsofhyperf/tcp-sender` is a TCP service component similar to `hyperf/websocket-server`, which can be used to send messages to a specified fd.
There's no need to worry about the underlying implementation; just focus on the business logic.

## Installation

```shell
composer require friendsofhyperf/tcp-sender
```

## Usage

### Configure config/autoload/servers.php

```php
'servers' => [
        [
            'name' => 'tcp',
            'type' => Server::SERVER_BASE,
            'host' => '0.0.0.0',
            'port' => 9401,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
                Event::ON_CONNECT => [TcpServer::class,'onConnect'],
                Event::ON_CLOSE => [TcpServer::class,'onClose'],
                Event::ON_RECEIVE => [TcpServer::class,'onReceive'],
            ],
            'options' => [
                // Whether to enable request lifecycle event
                'enable_request_lifecycle' => false,
            ],
        ]
    ],
```

### Asynchronous Style

```php
<?php

namespace App;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnReceiveInterface;
use FriendsOfHyperf\TcpSender\Sender;
use Swoole\Server;

class TcpServer implements OnCloseInterface,OnReceiveInterface
{
    public function __construct(private Sender $sender)
    {
    }

    /**
     * @param Server $server
     */
    public function onConnect($server, $fd, $reactorId): void
    {
        $server->send($fd, sprintf('Client %s connected.'.PHP_EOL, $fd));
    }

    public function onClose($server, int $fd, int $reactorId): void
    {
        $server->send($fd, sprintf('Client %s closed.'.PHP_EOL, $fd));
    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        $server->send($fd, sprintf('Client %s send: %s'.PHP_EOL, $fd, $data));
        var_dump($data);
    }


}
```

### Coroutine Style

```php
namespace App;

use Hyperf\Contract\OnReceiveInterface;
use FriendsOfHyperf\TcpSender\Sender;
use Swoole\Coroutine\Server\Connection;
use Swoole\Server;

class TcpServer implements OnReceiveInterface
{
    public function __construct(private Sender $sender)
    {
    }

    public function onConnect(Connection $connection, $fd): void
    {
        // Set the mapping between fd and connection
        $this->sender->setResponse($fd,$connection);
        $connection->send(sprintf('Client %s connected.'.PHP_EOL, $fd));
    }

    public function onClose($connection, int $fd): void
    {
        // Remove the mapping between fd and connection
        $this->sender->setResponse($fd,null);
    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        $server->send($fd, sprintf('Client %s send: %s'.PHP_EOL, $fd, $data));
    }


}
```

## Usage in Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use FriendsOfHyperf\TcpSender\Sender;

class IndexController extends AbstractController
{
    public function __construct(private Sender $sender)
    {
    }

    public function index()
    {
        // Send a message to the specified fd
        $this->sender->send(1, 'Hello Hyperf.');
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}

```