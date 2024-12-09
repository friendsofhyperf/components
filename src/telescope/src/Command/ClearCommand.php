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

use FriendsOfHyperf\Telescope\Contract\ClearableRepository;
use Hyperf\Command\Command;

class ClearCommand extends Command
{
    public function __construct(private ClearableRepository $storage)
    {
        parent::__construct('telescope:clear');
    }

    public function handle()
    {
        $this->storage->clear();

        $this->info('Telescope entries cleared!');
    }
}
