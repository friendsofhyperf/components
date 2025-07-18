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

use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Interfaces\SecurityInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use RuntimeException;

/**
 * @internal
 */
trait AuthorizationRequestResolveEventFactoryTrait
{
    private ScopeConverterInterface $scopeConverter;

    private ClientManagerInterface $clientManager;

    private SecurityInterface $security;

    public function fromAuthorizationRequest(AuthorizationRequestInterface $authorizationRequest): AuthorizationRequestResolveEvent
    {
        $scopes = $this->scopeConverter->toDomainArray(array_values($authorizationRequest->getScopes()));

        $client = $this->clientManager->find($authorizationRequest->getClient()->getIdentifier());

        if ($client === null) {
            throw new RuntimeException(\sprintf('No client found for the given identifier \'%s\'.', $authorizationRequest->getClient()->getIdentifier()));
        }

        $user = $this->security->getUser();
        if ($user === null) {
            throw new RuntimeException('A logged in user is required to resolve the authorization request.');
        }

        return new AuthorizationRequestResolveEvent($authorizationRequest, $scopes, $client, $user);
    }
}
