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

use Hyperf\Command\Command;
use Hyperf\DbConnection\Db;

use function Hyperf\Config\config;

class ClearCommand extends Command
{
    protected ?string $signature = 'telescope:clear';

    public function handle()
    {
        $connection = config('telescope.database.connection');
        Db::connection($connection)->table('telescope_entries')->delete();
        Db::connection($connection)->table('telescope_entries_tags')->delete();
        Db::connection($connection)->table('telescope_monitoring')->delete();
    }
}
