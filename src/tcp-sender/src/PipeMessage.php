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
            if (! $sender = $this->get(Sender::class)) {
                return;
            }

            $params = $sender->getFdAndMethodFromProxyMethod($this->method, $this->arguments);

            $sender->proxy(...$params);
        } catch (Throwable $exception) {
            $this->get(StdoutLoggerInterface::class)?->warning((string) $exception);
        }
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T|null
     */
    private function get(string $class)
    {
        if (! ApplicationContext::hasContainer()) {
            return null;
        }

        $container = ApplicationContext::getContainer();

        if (! $container->has($class)) {
            return null;
        }

        return $container->get($class);
    }
}
