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

use FriendsOfHyperf\IpcBroadcaster\IpcMessage;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Psr\Container\ContainerInterface;
use Throwable;

class PipeMessage extends IpcMessage
{
    public function __construct(
        private string $method,
        private array $arguments,
    ) {
    }

    public function handle(): void
    {
        try {
            if (! $sender = $this->getSender()) {
                return;
            }

            $params = $sender->getFdAndMethodFromProxyMethod($this->method, $this->arguments);

            $sender->proxy(...$params);
        } catch (Throwable $exception) {
            $this->getLogger()?->warning((string) $exception);
        }
    }

    private function getLogger(): ?StdoutLoggerInterface
    {
        if (! $container = $this->getContainer()) {
            return null;
        }

        if (! $container->has(StdoutLoggerInterface::class)) {
            return null;
        }

        return $container->get(StdoutLoggerInterface::class);
    }

    private function getSender(): ?Sender
    {
        if (! $container = $this->getContainer()) {
            return null;
        }

        if (! $container->has(Sender::class)) {
            return null;
        }

        return $container->get(Sender::class);
    }

    private function getContainer(): ?ContainerInterface
    {
        if (! ApplicationContext::hasContainer()) {
            return null;
        }

        return ApplicationContext::getContainer();
    }
}
