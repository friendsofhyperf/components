<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster\Listener;

use FriendsOfHyperf\IpcBroadcaster\Contract\CanBeSetOrGetFromWorkerId;
use FriendsOfHyperf\IpcBroadcaster\Contract\IpcMessageInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Throwable;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof OnPipeMessage && $event->data instanceof IpcMessageInterface) {
            /** @var IpcMessageInterface $message */
            $message = $event->data;

            try {
                if ($message instanceof CanBeSetOrGetFromWorkerId) {
                    $message->setFromWorkerId($event->fromWorkerId);
                }
                $message->handle();
            } catch (Throwable $exception) {
                $this->logger->warning((string) $exception);
            }
        }
    }
}
