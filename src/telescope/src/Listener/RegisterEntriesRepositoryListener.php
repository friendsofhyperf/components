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

use function Hyperf\Support\make;

class RegisterEntriesRepositoryListener implements ListenerInterface
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
        /** @var array<string, array{driver?: class-string}> */
        $drivers = (array) $this->config->get('telescope.storage', []);

        foreach ($drivers as $driver => $options) {
            $driver = $options['driver'] ?? DatabaseEntriesRepository::class;
            $this->manager->register($driver, make($options['driver']));
        }
    }
}
