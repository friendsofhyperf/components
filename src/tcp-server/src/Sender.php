<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TcpServer;

use FriendsOfHyperf\TcpServer\Exception\InvalidMethodException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Server\CoroutineServer;
use Swoole\Http\Response;
use Swoole\Server;
use Swow\Psr7\Server\ServerConnection;

class Sender
{
    /**
     * @var Response[]|ServerConnection[]
     */
    protected array $responses = [];

    private int $workerId;

    public function __construct(
        private readonly StdoutLoggerInterface $logger,
        private readonly ConfigInterface $config,
        private readonly ContainerInterface $container,
    ) {
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

    public function check($fd): bool
    {
        $info = $this->getServer()->connection_info($fd);

        if (($info['socket_type'] ?? null) === SWOOLE_SOCK_TCP) {
            return true;
        }

        return false;
    }

    public function proxy(int $fd, string $method, array $arguments): bool
    {
        $result = $this->check($fd);
        if ($result) {
            /** @var \Swoole\WebSocket\Server $server */
            $server = $this->getServer();
            $result = $server->{$method}(...$arguments);
            $this->logger->debug(
                sprintf(
                    "[WebSocket] Worker.{$this->workerId} send to #{$fd}.Send %s",
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

        return [$arguments, $method];
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
                $this->logger->debug("[WebSocket] Let Worker.{$workerId} try to {$name}.");
            }
        }
    }
}
