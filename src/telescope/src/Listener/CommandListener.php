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

use FriendsOfHyperf\Telescope\IncomingEntry;
use FriendsOfHyperf\Telescope\Telescope;
use FriendsOfHyperf\Telescope\TelescopeConfig;
use FriendsOfHyperf\Telescope\TelescopeContext;
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @property \Symfony\Component\Console\Input\InputInterface $input
 * @property int $exitCode
 */
class CommandListener implements ListenerInterface
{
    public function __construct(private TelescopeConfig $telescopeConfig)
    {
    }

    public function listen(): array
    {
        return [
            AfterExecute::class,
        ];
    }

    /**
     * @param AfterExecute|object $event
     */
    public function process(object $event): void
    {
        if (
            ! $event instanceof AfterExecute
            || ! $this->telescopeConfig->isEnable('command')
        ) {
            return;
        }

        $command = $event->getCommand();

        if (in_array($command->getName(), $this->telescopeConfig->getIgnoreCommands())) {
            return;
        }

        $arguments = (fn () => $this->input->getArguments())->call($command);
        $options = (fn () => $this->input->getOptions())->call($command);
        $exitCode = (fn () => $this->exitCode)->call($command);

        TelescopeContext::getOrSetBatch((string) TelescopeContext::getBatchId());

        Telescope::recordCommand(IncomingEntry::make([
            'command' => $command->getName(),
            'exit_code' => $exitCode,
            'arguments' => $arguments,
            'options' => $options,
        ]));
    }
}
