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

use Exception;
use FriendsOfHyperf\TcpSender\Listener\OnPipeMessageListener;
use FriendsOfHyperf\TcpSender\Sender;
use FriendsOfHyperf\TcpSender\SenderPipeMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Swoole\Server;

/**
 * @internal
 * @coversNothing
 */
class OnPipeMessageListenerTest extends TestCase
{
    private OnPipeMessageListener $listener;

    private ContainerInterface $container;

    private StdoutLoggerInterface $logger;

    private Sender $sender;

    private FormatterInterface $formatter;

    private Server $server;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->logger = $this->createMock(StdoutLoggerInterface::class);
        $this->sender = $this->createMock(Sender::class);
        $this->server = $this->createMock(Server::class);
        $this->formatter = $this->createMock(FormatterInterface::class);
        $this->container->method('get')->with(FormatterInterface::class)->willReturn($this->formatter);
        $this->formatter->method('format')->willReturn('formatted');
        $this->listener = new OnPipeMessageListener($this->container, $this->logger, $this->sender);
    }

    public function testProcessHandlesSenderPipeMessage(): void
    {
        $message = new SenderPipeMessage('method', [1, 'message']);
        $event = new OnPipeMessage($this->server, 1, $message);

        $this->sender->expects($this->once())->method('getFdAndMethodFromProxyMethod')->with('method', [1, 'message']);

        $this->listener->process($event);
    }

    public function testProcessHandlesException(): void
    {
        $message = new SenderPipeMessage('method', [1, 'message']);
        $event = new OnPipeMessage($this->server, 1, $message);

        $this->sender->method('getFdAndMethodFromProxyMethod')
            ->willThrowException(new Exception());
        $this->container->method('get')->willReturn($this->formatter);
        $this->logger->expects($this->once())->method('warning');

        $this->listener->process($event);
    }

    public function testProcessIgnoresNonSenderPipeMessage(): void
    {
        $event = new OnPipeMessage($this->server, 1, new stdClass());

        $this->sender->expects($this->never())->method('getFdAndMethodFromProxyMethod');
        $this->sender->expects($this->never())->method('proxy');

        $this->listener->process($event);
    }
}
