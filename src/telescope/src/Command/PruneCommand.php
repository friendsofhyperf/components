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
use FriendsOfHyperf\Telescope\Contract\PrunableRepository;
use Hyperf\Command\Command;

class PruneCommand extends Command
{
    protected ?string $signature = 'telescope:prune {--hours=24 : The number of hours to retain Telescope data} {--keep-exceptions : Retain exception data}';

    public function __construct(private PrunableRepository $storage)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info(
            $this->storage->prune(
                Carbon::now()->subHours((int) $this->option('hours')),
                $this->option('keep-exceptions')
            ) . ' entries pruned.'
        );
    }
}
