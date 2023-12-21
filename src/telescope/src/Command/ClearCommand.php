<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Command;

use FriendsOfHyperf\Telescope\Telescope;
use Hyperf\Command\Command;
use Hyperf\DbConnection\Db;

class ClearCommand extends Command
{
    protected ?string $signature = 'telescope:clear';

    public function handle()
    {
        $connection = Telescope::getConfig()->getDatabaseConnection();
        Db::connection($connection)->table('telescope_entries')->delete();
        Db::connection($connection)->table('telescope_entries_tags')->delete();
        Db::connection($connection)->table('telescope_monitoring')->delete();
    }
}
