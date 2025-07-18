<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Manager;

use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

interface ScopeManagerInterface
{
    public function find(string $identifier): ?Scope;

    public function save(Scope $scope): void;
}
