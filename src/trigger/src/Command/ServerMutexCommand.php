<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Command;

use Hyperf\Command\Command;

class ServerMutexCommand extends Command
{
    protected ?string $signature = 'trigger:server-mutex {--C|connection= : connection} {--L|list : list all server mutexes.}';

    protected string $description = 'Server mutex management.';

    public function handle()
    {
    }
}
