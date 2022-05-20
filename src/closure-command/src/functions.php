<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ClosureCommand;

use Closure;

if (! function_exists('FriendsOfHyperf\ClosureCommand\command')) {
    /**
     * @return ClosureCommand
     * @deprecated v2.0
     */
    function command(string $signature, Closure $callback)
    {
        return Console::command($signature, $callback);
    }
}

if (! function_exists('FriendsOfHyperf\ClosureCommand\commands')) {
    /**
     * @return ClosureCommand[]
     * @deprecated v2.0
     */
    function commands()
    {
        return Console::getCommands();
    }
}

if (! function_exists('FriendsOfHyperf\ClosureCommand\call')) {
    /**
     * @return int
     * @deprecated v2.0
     */
    function call(string $command, array $arguments = [])
    {
        return Console::call($command, $arguments);
    }
}
