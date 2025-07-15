<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Event;

use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use Symfony\Contracts\EventDispatcher\Event;

final class ScopeResolveEvent extends Event
{
    public function __construct(
        private array $scopes,
        private readonly Grant $grant,
        private readonly ClientInterface $client,
        private readonly string|int|null $userIdentifier
    ) {
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): void
    {
        $this->scopes = $scopes;
    }

    public function getGrant(): Grant
    {
        return $this->grant;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getUserIdentifier(): int|string|null
    {
        return $this->userIdentifier;
    }
}
