<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use FriendsOfHyperf\IpcBroadcaster\Contract\IpcMessageInterface;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;

class PipeMessage implements IpcMessageInterface
{
    public function __construct(public bool $recording)
    {
    }

    public function handle(): void
    {
        $this->getConfig()?->set('telescope.recording', $this->recording);
    }

    private function getConfig(): ?ConfigInterface
    {
        if (! ApplicationContext::hasContainer()) {
            return null;
        }

        if (! ApplicationContext::getContainer()->has(ConfigInterface::class)) {
            return null;
        }

        return ApplicationContext::getContainer()->get(ConfigInterface::class);
    }
}
