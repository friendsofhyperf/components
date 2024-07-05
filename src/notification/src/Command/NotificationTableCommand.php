<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notification\Command;

use Hyperf\Command\Command;

class NotificationTableCommand extends Command
{
    protected ?string $signature = 'notification:table';

    protected string $description = 'Create a migration for the notifications table';

    public function handle()
    {
        copy(__DIR__ . '/Stubs/2021_04_18_224626_notifications_table.stub', BASE_PATH . '/migrations/2021_04_18_224626_notifications_table.php');
        $this->output->success('Migration created successfully!');
    }
}
