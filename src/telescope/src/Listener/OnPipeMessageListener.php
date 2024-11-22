<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\PipeMessage;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnPipeMessage;
use Hyperf\Process\Event\PipeMessage as OnProcessPipMessage;

class OnPipeMessageListener implements ListenerInterface
{
    public function __construct(
        private ConfigInterface $config
    ) {
    }

    public function listen(): array
    {
        return [
            OnPipeMessage::class,
            OnProcessPipMessage::class,
        ];
    }

    /**
     * @param OnPipeMessage|OnProcessPipMessage $event
     */
    public function process(object $event): void
    {
        if (
            property_exists($event, 'data')
            && $event->data instanceof PipeMessage
        ) {
            $message = $event->data;

            $this->config->set('telescope.recording', (bool) $message->recording);
        }
    }
}
