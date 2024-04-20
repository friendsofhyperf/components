<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\TcpSender;

use FriendsOfHyperf\TcpSender\Exception\InvalidMethodException;
use FriendsOfHyperf\TcpSender\Sender;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use PHPUnit\Framework\TestCase;
use Swoole\Server;

/**
 * @internal
 * @coversNothing
 */
class SenderTest extends TestCase
{
    private Sender $sender;

    private StdoutLoggerInterface $logger;

    private ConfigInterface $config;

    private ContainerInterface $container;

    private Server $server;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(StdoutLoggerInterface::class);
        $this->config = $this->createMock(ConfigInterface::class);
        $this->container = $this->createMock(ContainerInterface::class);
        $this->server = $this->createMock(Server::class);

        $this->container->method('get')->willReturn($this->server);

        $this->sender = new Sender($this->logger, $this->config, $this->container);
    }

    public function testSetAndGetWorkerId(): void
    {
        $this->sender->setWorkerId(1);
        $this->assertEquals(1, $this->sender->getWorkerId());
    }

    public function testIsCoroutineServer(): void
    {
        $this->config->method('get')->willReturn('Hyperf\Server\CoroutineServer');
        $this->assertTrue($this->sender->isCoroutineServer());
    }

    public function testCheckWithTcpSocket(): void
    {
        $this->server->method('connection_info')->willReturn(['socket_type' => SWOOLE_SOCK_TCP]);
        $this->assertTrue($this->sender->check(1));
    }

    public function testCheckWithNonTcpSocket(): void
    {
        $this->server->method('connection_info')->willReturn(['socket_type' => SWOOLE_SOCK_UDP]);
        $this->assertFalse($this->sender->check(1));
    }

    public function testProxyWithSuccessfulSend(): void
    {
        $this->server->method('send')->willReturn(true);
        $this->server->method('connection_info')->willReturn(['socket_type' => SWOOLE_SOCK_TCP]);
        $this->sender->setWorkerId(1);
        $this->assertTrue($this->sender->proxy('send', 1, [1, 'message']));
    }

    public function testProxyWithFailedSend(): void
    {
        $this->server->method('send')->willReturn(false);
        $this->assertFalse($this->sender->proxy('send', 1, ['message']));
    }

    public function testGetFdAndMethodFromProxyMethod(): void
    {
        $this->assertEquals(['send', 1, 'message'], $this->sender->getFdAndMethodFromProxyMethod('send', [1, 'message']));
    }

    public function testGetFdAndMethodFromProxyMethodWithInvalidMethod(): void
    {
        $this->expectException(InvalidMethodException::class);
        $this->sender->getFdAndMethodFromProxyMethod('invalid', [1, 'message']);
    }

    public function testSetAndGetResponse(): void
    {
        $response = 1;
        $this->sender->setResponse(1, $response);
        $this->assertSame($response, $this->sender->getResponse(1));
    }

    public function testGetResponseWithNoResponseSet(): void
    {
        $this->assertNull($this->sender->getResponse(1));
    }
}
