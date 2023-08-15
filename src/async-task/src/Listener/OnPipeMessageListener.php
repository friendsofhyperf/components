<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AsyncTask\Listener;

use FriendsOfHyperf\AsyncTask\Task;
use FriendsOfHyperf\AsyncTask\TaskMessage;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Psr\Container\ContainerInterface;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
        ];
    }

    /**
     * @param OnPipeMessage $event
     */
    public function process(object $event): void
    {
        $message = $event->data;

        if (! $message instanceof TaskMessage) {
            return;
        }

        Task::execute($message);
    }
}
