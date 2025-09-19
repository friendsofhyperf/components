<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Repository;

use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Event\ScopeResolveEvent;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ScopeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Grant;
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class ScopeRepository implements ScopeRepositoryInterface
{
    public function __construct(
        private readonly ScopeManagerInterface $scopeManager,
        private readonly ClientManagerInterface $clientManager,
        private readonly ScopeConverterInterface $scopeConverter,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        $scope = $this->scopeManager->find($identifier);

        if ($scope === null) {
            return null;
        }

        return $this->scopeConverter->toLeague($scope);
    }

    /**
     * @param ScopeEntityInterface[] $scopes
     * @param non-empty-string $grantType
     *
     * @return list<ScopeEntityInterface>
     * @throws OAuthServerException
     */
    public function finalizeScopes(
        array $scopes,
        string $grantType,
        ClientEntityInterface $clientEntity,
        null|string|int $userIdentifier = null,
        ?string $authCodeId = null,
    ): array {
        /** @var ClientInterface $client */
        $client = $this->clientManager->find($clientEntity->getIdentifier());

        $scopes = $this->setupScopes($client, $this->scopeConverter->toDomainArray(array_values($scopes)));

        $event = $this->eventDispatcher->dispatch(
            new ScopeResolveEvent(
                $scopes,
                new Grant($grantType),
                $client,
                $userIdentifier
            )
        );

        return $this->scopeConverter->toLeagueArray($event->getScopes());
    }

    /**
     * @param list<Scope> $requestedScopes
     *
     * @return list<Scope>
     * @throws OAuthServerException
     */
    private function setupScopes(ClientInterface $client, array $requestedScopes): array
    {
        $clientScopes = $client->getScopes();

        if (empty($clientScopes)) {
            return $requestedScopes;
        }

        if (empty($requestedScopes)) {
            return $clientScopes;
        }

        $finalizedScopes = [];
        $clientScopesAsStrings = array_map('strval', $clientScopes);

        foreach ($requestedScopes as $requestedScope) {
            $requestedScopeAsString = (string) $requestedScope;
            if (! \in_array($requestedScopeAsString, $clientScopesAsStrings, true)) {
                throw OAuthServerException::invalidScope($requestedScopeAsString);
            }

            $finalizedScopes[] = $requestedScope;
        }

        return $finalizedScopes;
    }
}
