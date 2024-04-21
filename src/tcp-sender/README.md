# TcpSender

[![Latest Stable Version](https://poser.pugx.org/friendsofhyperf/tcp-sender/version.png)](https://packagist.org/packages/friendsofhyperf/tcp-sender)
[![Total Downloads](https://poser.pugx.org/friendsofhyperf/tcp-sender/d/total.png)](https://packagist.org/packages/friendsofhyperf/tcp-sender)
[![GitHub license](https://img.shields.io/github/license/friendsofhyperf/tcp-sender)](https://github.com/friendsofhyperf/tcp-sender)

Another TcpSender component for Hyperf.

## Installation

- Installation

```shell
composer require friendsofhyperf/tcp-sender
```

## Usage

### config/autoload/servers.php

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

### 多进程模型

#### TcpServer

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

### 协程风格服务 单进程模型

#### TcpServer

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
        // 设置 fd 和 connection 的映射关系
        $this->sender->setResponse($fd,$connection);
        $connection->send(sprintf('Client %s connected.'.PHP_EOL, $fd));
    }

    public function onClose($connection, int $fd): void
    {
        // 删除 fd 和 connection 的映射关系
        $this->sender->setResponse($fd,null);
    }

    public function onReceive($server, int $fd, int $reactorId, string $data): void
    {
        $server->send($fd, sprintf('Client %s send: %s'.PHP_EOL, $fd, $data));
    }


}
```

## YourService or YourController

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
        // 向指定的fd发送消息
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

## Contact

- [Twitter](https://twitter.com/huangdijia)
- [Gmail](mailto:huangdijia@gmail.com)

## License

[MIT](LICENSE)
