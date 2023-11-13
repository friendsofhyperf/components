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
use FriendsOfHyperf\Telescope\SwitchManager;
use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Command\Event\AfterExecute;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @property \Symfony\Component\Console\Input\InputInterface $input
 * @property int $exitCode
 */
class CommandListener implements ListenerInterface
{
    public function __construct(private SwitchManager $switchManager)
    {
    }

    public function listen(): array
    {
        return [
            AfterExecute::class,
        ];
    }

    /**
     * @param AfterExecute $event
     */
    public function process(object $event): void
    {
        if ($this->switchManager->isEnable('command') === false) {
            return;
        }

        $command = $event->getCommand();
        $arguments = (fn () => $this->input->getArguments())->call($command);
        $options = (fn () => $this->input->getOptions())->call($command);
        $name = $command->getName();
        $exitCode = (fn () => $this->exitCode)->call($command);

        Telescope::recordCommand(IncomingEntry::make([
            'command' => $name,
            'exit_code' => $exitCode,
            'arguments' => $arguments,
            'options' => $options,
        ]));
    }
}
