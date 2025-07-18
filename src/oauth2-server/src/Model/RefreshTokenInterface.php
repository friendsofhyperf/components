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

interface RefreshTokenInterface
{
    public function __toString(): string;

    public function getIdentifier(): string;

    public function getExpiry(): DateTimeInterface;

    public function getAccessToken(): ?AccessTokenInterface;

    public function isRevoked(): bool;

    public function revoke(): self;
}
