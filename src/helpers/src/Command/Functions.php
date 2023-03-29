<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Helpers\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Call command quickly.
 * @throws TypeError
 * @throws Exception
 */
function call(string $command, array $arguments = []): int
{
    $arguments['command'] = $command;
    $input = new ArrayInput($arguments);
    $output = new NullOutput();

    /** @var \Symfony\Component\Console\Application $application */
    $application = di(ApplicationInterface::class);
    $application->setAutoExit(false);

    return $application->run($input, $output);
}
