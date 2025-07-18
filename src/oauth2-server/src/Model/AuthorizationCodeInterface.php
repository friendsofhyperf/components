<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Model;

use DateTimeInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;

interface AuthorizationCodeInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;

    public function getExpiryDateTime(): DateTimeInterface;

    public function getUserIdentifier(): ?string;

    public function getClient(): ClientInterface;

    /**
     * @return list<Scope>
     */
    public function getScopes(): array;

    public function isRevoked(): bool;

    public function revoke(): self;
}
