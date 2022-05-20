<?php

declare(strict_types=1);
/**
 * This file is part of closure-command.
 *
 * @link     https://github.com/friendsofhyperf/closure-command
 * @document https://github.com/friendsofhyperf/closure-command/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\ClosureCommand\Console;
use FriendsOfHyperf\ClosureCommand\Inspiring;

Console::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');
