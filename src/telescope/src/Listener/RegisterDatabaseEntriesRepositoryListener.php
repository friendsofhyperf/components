<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Listener;

use FriendsOfHyperf\Telescope\Storage\DatabaseEntriesRepository;
use FriendsOfHyperf\Telescope\Storage\EntriesRepositoryManager;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class RegisterDatabaseEntriesRepositoryListener implements ListenerInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected EntriesRepositoryManager $manager
    ) {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $connection = $this->config->get('telescope.storage.database.connection', 'default');
        $chunkSize = $this->config->get('telescope.storage.database.chunk', 1000);

        $this->manager->register('database', new DatabaseEntriesRepository($connection, $chunkSize));
    }
}
