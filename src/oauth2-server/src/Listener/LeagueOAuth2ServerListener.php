<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Listener;

use FriendsOfHyperf\Oauth2\Server\Interfaces\ConfigInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ScopeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Database\Migrations\Migrator;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;

class LeagueOAuth2ServerListener implements ListenerInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ConfigInterface $config,
        private readonly Migrator $migrator
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
        $this->configureScopes();
        $this->configureMigration();
    }

    private function configureMigration(): void
    {
        $this->migrator->path(dirname(__DIR__, 2) . '/databases');
    }

    private function configureScopes(): void
    {
        foreach ($this->config->get('scopes.available', []) as $scope) {
            $this->container->get(ScopeManagerInterface::class)->save(new Scope($scope));
        }
    }
}
