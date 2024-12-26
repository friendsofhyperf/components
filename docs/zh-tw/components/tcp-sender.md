# Tcp Sender

## 簡介

`friendsofhyperf/tcp-sender` 是一個類似 `hyperf/websocket-server` 的 TCP 服務元件，可以用於向指定的 fd 傳送訊息。
無需額外關注底層的實現，只需關注業務邏輯即可。


## 安裝

```shell
composer require friendsofhyperf/tcp-sender
```

## 使用

### 配置 config/autoload/servers.php

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

### 非同步風格

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

### 協程風格

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
        // 設定 fd 和 connection 的對映關係
        $this->sender->setResponse($fd,$connection);
        $connection->send(sprintf('Client %s connected.'.PHP_EOL, $fd));
    }

    public function onClose($connection, int $fd): void
    {
        // 刪除 fd 和 connection 的對映關係
        $this->sender->setResponse($fd,null);
    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        $server->send($fd, sprintf('Client %s send: %s'.PHP_EOL, $fd, $data));
    }


}
```

## 在控制器中使用

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
        // 向指定的fd傳送訊息
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
