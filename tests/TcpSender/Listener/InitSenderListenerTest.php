<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\TcpSender\Listener;

use FriendsOfHyperf\TcpSender\Listener\InitSenderListener;
use FriendsOfHyperf\TcpSender\Sender;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Framework\Event\AfterWorkerStart;
use PHPUnit\Framework\TestCase;
use Swoole\Server;

/**
 * @internal
 * @coversNothing
 */
class InitSenderListenerTest extends TestCase
{
    private InitSenderListener $listener;

    private ContainerInterface $container;

    private Sender $sender;

    private Server $server;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->sender = $this->createMock(Sender::class);
        $this->server = $this->createMock(Server::class);
        $this->listener = new InitSenderListener($this->container);
    }

    public function testSenderIsSetWithWorkerIdWhenContainerHasSender(): void
    {
        $this->container->method('has')->willReturn(true);
        $this->container->method('get')->willReturn($this->sender);
        $this->sender->expects($this->once())->method('setWorkerId')->with($this->equalTo(1));

        $this->listener->process(new AfterWorkerStart($this->server, 1));
    }

    public function testSenderIsNotSetWithWorkerIdWhenContainerDoesNotHaveSender(): void
    {
        $this->container->method('has')->willReturn(false);
        $this->sender->expects($this->never())->method('setWorkerId');

        $this->listener->process(new AfterWorkerStart($this->server, 1));
    }
}
