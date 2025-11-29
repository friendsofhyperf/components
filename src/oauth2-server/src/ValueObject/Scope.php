<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\ValueObject;

use Stringable;

final class Scope implements Stringable
{
    public function __construct(
        private readonly string $scope
    ) {
    }

    public function __toString(): string
    {
        return $this->scope;
    }
}
