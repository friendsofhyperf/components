<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager\InMemory;

use FriendsOfHyperf\Oauth2\Server\Manager\ScopeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

class ScopeManager implements ScopeManagerInterface
{
    /**
     * @var array<string, Scope>
     */
    private array $scopes = [];

    public function __construct()
    {
    }

    public function find(string $identifier): ?Scope
    {
        return $this->scopes[$identifier];
    }

    public function save(Scope $scope): void
    {
        $this->scopes[(string) $scope] = $scope;
    }
}
