<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Notifications\Command;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as Base;

#[Command(name: 'notification:table', description: 'Create a migration for the notifications table', aliases: ['notification:table'])]
class NotificationTableCommandTableCommand extends Base
{
    /**
     * The command name.
     */
    protected ?string $name = 'notification:table';

    /**
     * The command description.
     */
    protected string $description = 'Create a migration for the notifications table';

    /**
     * The command aliases.
     */
    private array $aliases = ['notification:table'];

    public function __invoke(): void
    {
        copy(__DIR__ . '/Stubs/2021_04_18_224626_notifications_table.php', BASE_PATH . '/migrations/2021_04_18_224626_notifications_table.php');
        $this->output->success('Migration created successfully!');
    }
}
