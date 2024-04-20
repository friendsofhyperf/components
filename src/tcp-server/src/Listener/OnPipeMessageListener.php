<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TcpServer\Listener;

use FriendsOfHyperf\TcpServer\Sender;
use FriendsOfHyperf\TcpServer\SenderPipeMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Psr\Container\ContainerInterface;
use Throwable;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly StdoutLoggerInterface $logger,
        private Sender $sender
    ) {
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof OnPipeMessage && $event->data instanceof SenderPipeMessage) {
            /** @var SenderPipeMessage $message */
            $message = $event->data;

            try {
                [$fd, $method] = $this->sender->getFdAndMethodFromProxyMethod($message->method, $message->args);
                $this->sender->proxy($fd, $method, $message->args);
            } catch (Throwable $exception) {
                $formatter = $this->container->get(FormatterInterface::class);
                $this->logger->warning($formatter->format($exception));
            }
        }
    }
}
