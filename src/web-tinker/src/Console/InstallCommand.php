<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker\Console;

use Hyperf\Command\Command;

class InstallCommand extends Command
{
    protected ?string $signature = 'web-tinker:install';

    protected string $description = 'Install all of the Web Tinker resources';

    public function handle()
    {
        $this->comment('Publishing Web Tinker Assets...');

        $this->call('vendor:publish', [
            'package' => 'friendsofhyperf/web-tinker',
        ]);

        $this->info('Web tinker installed successfully.');
    }
}
