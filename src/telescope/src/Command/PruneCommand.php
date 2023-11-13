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

use Carbon\Carbon;
use Hyperf\Command\Command;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;

use function Hyperf\Config\config;

class PruneCommand extends Command
{
    protected ?string $signature = 'telescope:prune {--hours=24 : The number of hours to retain Telescope data}';

    public function __construct(private ContainerInterface $container)
    {
        parent::__construct();
    }

    public function handle()
    {
        $connection = config('telescope.database.connection');
        $created_at = Carbon::now()->subHours($this->input->getOption('hours'));
        Db::connection($connection)->table('telescope_entries')
            ->where('created_at', '<', $created_at)
            ->delete();
        Db::connection($connection)
            ->table('telescope_entries_tags')
            ->delete();
        Db::connection($connection)
            ->table('telescope_monitoring')
            ->delete();
    }
}
