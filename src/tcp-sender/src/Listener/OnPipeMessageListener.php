<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\TcpSender\Listener;

use FriendsOfHyperf\TcpSender\Sender;
use FriendsOfHyperf\TcpSender\SenderPipeMessage;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Psr\Container\ContainerInterface;
use Throwable;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(
        protected ContainerInterface $container,
        protected StdoutLoggerInterface $logger,
        protected Sender $sender
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
                $params = $this->sender->getFdAndMethodFromProxyMethod($message->method, $message->args);
                $this->sender->proxy(...$params);
            } catch (Throwable $exception) {
                $this->logger->warning((string) $exception);
            }
        }
    }
}
