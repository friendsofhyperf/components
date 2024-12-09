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

class InstallCommand extends Command
{
    public function __construct()
    {
        parent::__construct('telescope:install');
    }

    public function handle()
    {
        if (! $this->call('vendor:publish', ['package' => 'friendsofhyperf/telescope'])) {
            $this->info('publish successfully');
        } else {
            $this->error('publish failed');
        }

        if (! $this->call('migrate')) {
            $this->info('migrate successfully');
        } else {
            $this->error('migrate failed');
        }
    }
}
