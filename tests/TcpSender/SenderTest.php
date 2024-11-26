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

use FriendsOfHyperf\IpcBroadcaster\Contract\BroadcasterInterface;
use FriendsOfHyperf\TcpSender\Exception\InvalidMethodException;
use FriendsOfHyperf\TcpSender\Sender;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Swoole\Server;

beforeEach(function () {
    $this->logger = $this->createMock(StdoutLoggerInterface::class);
    $this->config = $this->createMock(ConfigInterface::class);
    $this->container = $this->createMock(ContainerInterface::class);
    $this->server = $this->createMock(Server::class);
    $this->broadcaster = $this->createMock(BroadcasterInterface::class);

    $this->container->method('get')->willReturn($this->server);

    $this->sender = new Sender(
        $this->container,
        $this->config,
        $this->broadcaster,
        $this->logger,
    );
});

test('test SetAndGetWorkerId', function () {
    $this->sender->setWorkerId(1);
    $this->assertEquals(1, $this->sender->getWorkerId());
});

test('test CheckWithTcpSocket', function () {
    $this->server->method('connection_info')->willReturn(['socket_type' => SWOOLE_SOCK_TCP]);
    $this->assertTrue($this->sender->check(1));
});

test('test CheckWithNonTcpSocket', function () {
    $this->server->method('connection_info')->willReturn(['socket_type' => SWOOLE_SOCK_UDP]);
    $this->assertFalse($this->sender->check(1));
});

test('test ProxyWithSuccessfulSend', function () {
    $this->server->method('send')->willReturn(true);
    $this->server->method('connection_info')->willReturn(['socket_type' => SWOOLE_SOCK_TCP]);
    $this->sender->setWorkerId(1);
    $this->assertTrue($this->sender->proxy('send', 1, [1, 'message']));
});

test('test ProxyWithFailedSend', function () {
    $this->server->method('send')->willReturn(false);
    $this->assertFalse($this->sender->proxy('send', 1, ['message']));
});

test('test GetFdAndMethodFromProxyMethod', function () {
    $this->assertEquals(['send', 1, 'message'], $this->sender->getFdAndMethodFromProxyMethod('send', [1, 'message']));
});

test('test GetFdAndMethodFromProxyMethodWithInvalidMethod', function () {
    $this->expectException(InvalidMethodException::class);
    $this->sender->getFdAndMethodFromProxyMethod('invalid', [1, 'message']);
});

test('test SetAndGetResponse', function () {
    $response = 1;
    $this->sender->setResponse(1, $response);
    $this->assertSame($response, $this->sender->getResponse(1));
});

test('test GetResponseWithNoResponseSet', function () {
    $this->assertNull($this->sender->getResponse(1));
});
