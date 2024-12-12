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
    protected ?string $signature = 'telescope:install --driver=database';

    public function handle()
    {
        if (! $this->call('vendor:publish', [
            'package' => 'friendsofhyperf/telescope',
            '--id' => 'config',
        ])) {
            $this->info('publish config successfully');
        } else {
            $this->error('publish config failed');
        }

        if ($this->option('driver') === 'database') {
            if (! $this->call('vendor:publish', [
                'package' => 'friendsofhyperf/telescope',
                '--id' => 'migrations',
            ])) {
                $this->info('publish migrations successfully');
            } else {
                $this->error('publish migrations failed');
            }

            if (! $this->call('migrate')) {
                $this->info('migrate successfully');
            } else {
                $this->error('migrate failed');
            }
        }
    }
}
