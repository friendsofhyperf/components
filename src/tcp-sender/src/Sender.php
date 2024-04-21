<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TcpSender;

use FriendsOfHyperf\TcpSender\Exception\InvalidMethodException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\CoroutineServer;
use Swoole\Http\Response;
use Swoole\Server;
use Swow\Psr7\Server\ServerConnection;

/**
 * @method bool close(int $fd, bool $reset = false)
 * @method bool send(int|string $fd, string $data, int $serverSocket = -1)
 * @method bool sendfile(int $fd, string $filename, int $offset = 0, int $length = 0)
 * @method bool sendwait(int $fd, string $data)
 */
class Sender
{
    /**
     * @var Response[]|ServerConnection[]
     */
    protected array $responses = [];

    private int $workerId = 0;

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface $config,
        protected StdoutLoggerInterface $logger,
    ) {
    }

    public function __call($name, $arguments)
    {
        $params = $this->getFdAndMethodFromProxyMethod($name, $arguments);
        $fd = $params[1] ?? 0;
        $method = $params[0] ?? null;

        if ($this->isCoroutineServer()) {
            if ($response = $this->getResponse($fd)) {
                array_shift($arguments);
                $result = $response->{$method}(...$arguments);
                $this->logger->debug(
                    sprintf(
                        "[Tcp] Worker send to #{$fd}.Send %s",
                        $result ? 'success' : 'failed'
                    )
                );

                return $result;
            }

            return false;
        }

        if (! $this->proxy($method, $fd, $arguments)) {
            $this->sendPipeMessage($name, $arguments);
        }

        return true;
    }

    public function setWorkerId(int $workerId): void
    {
        $this->workerId = $workerId;
    }

    public function getWorkerId(): int
    {
        return $this->workerId;
    }

    public function isCoroutineServer(): bool
    {
        return $this->config->get('server.type') === CoroutineServer::class;
    }

    public function check(int $fd): bool
    {
        $info = $this->getServer()->connection_info($fd);

        if (($info['socket_type'] ?? null) === SWOOLE_SOCK_TCP) {
            return true;
        }

        return false;
    }

    public function proxy(string $method, int $fd, array $arguments): bool
    {
        $result = $this->check($fd);

        if ($result) {
            /** @var \Swoole\WebSocket\Server $server */
            $server = $this->getServer();
            $result = $server->{$method}(...$arguments);
            $this->logger->debug(
                sprintf(
                    "[Socket] Worker.{$this->workerId} send to #{$fd}.Send %s",
                    $result ? 'success' : 'failed'
                )
            );
        }

        return $result;
    }

    public function getFdAndMethodFromProxyMethod(string $method, array $arguments): array
    {
        if (! in_array($method, ['send', 'sendfile', 'sendwait', 'close'])) {
            throw new InvalidMethodException(sprintf('Method [%s] is not allowed.', $method));
        }

        return [$method, ...$arguments];
    }

    public function setResponse(int $fd, mixed $response): void
    {
        if ($response === null) {
            unset($this->responses[$fd]);
        } else {
            $this->responses[$fd] = $response;
        }
    }

    /**
     * @return Response|ServerConnection|null
     */
    public function getResponse(int $fd): mixed
    {
        return $this->responses[$fd] ?? null;
    }

    protected function getServer(): Server
    {
        return $this->container->get(Server::class);
    }

    protected function sendPipeMessage(string $name, array $arguments): void
    {
        $server = $this->getServer();
        $workerCount = $server->setting['worker_num'] - 1;
        for ($workerId = 0; $workerId <= $workerCount; ++$workerId) {
            if ($workerId !== $this->workerId) {
                $server->sendMessage(new SenderPipeMessage($name, $arguments), $workerId);
                $this->logger->debug("[Socket] Let Worker.{$workerId} try to {$name}.");
            }
        }
    }
}
