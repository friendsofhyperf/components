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

use Carbon\Carbon;
use FriendsOfHyperf\Oauth2\Server\Converter\ScopeConverterInterface;
use FriendsOfHyperf\Oauth2\Server\Entity\AccessToken;
use FriendsOfHyperf\Oauth2\Server\Manager\AccessTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\AccessToken as AccessTokenModel;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

use function Hyperf\Tappable\tap;

final class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(
        private readonly AccessTokenManagerInterface $accessTokenManager,
        private readonly ClientManagerInterface $clientManager,
        private readonly ScopeConverterInterface $scopeConverter
    ) {
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, ?string $userIdentifier = null): AccessTokenEntityInterface
    {
        $accessToken = new AccessToken();
        $accessToken->setClient($clientEntity);
        if ($userIdentifier !== null && $userIdentifier !== '') {
            $accessToken->setUserIdentifier($userIdentifier);
        }

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        return $accessToken;
    }

    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $accessToken = $this->accessTokenManager->find($accessTokenEntity->getIdentifier());

        if ($accessToken !== null) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $accessToken = $this->buildAccessTokenModel($accessTokenEntity);

        $this->accessTokenManager->save($accessToken);
    }

    public function revokeAccessToken(string $tokenId): void
    {
        $accessToken = $this->accessTokenManager->find($tokenId);

        if ($accessToken === null) {
            return;
        }

        $accessToken->revoke();

        $this->accessTokenManager->save($accessToken);
    }

    public function isAccessTokenRevoked(string $tokenId): bool
    {
        $accessToken = $this->accessTokenManager->find($tokenId);

        if ($accessToken === null) {
            return true;
        }

        return $accessToken->isRevoked();
    }

    private function buildAccessTokenModel(AccessTokenEntityInterface $accessTokenEntity): AccessTokenModel
    {
        /** @var ClientInterface $client */
        $client = $this->clientManager->find($accessTokenEntity->getClient()->getIdentifier());

        $userIdentifier = $accessTokenEntity->getUserIdentifier();
        return tap(new AccessTokenModel(), function (AccessTokenModel $accessToken) use ($accessTokenEntity, $client, $userIdentifier) {
            $accessToken->id = $accessTokenEntity->getIdentifier();
            $accessToken->client_id = $client->getIdentifier();
            $accessToken->user_id = $userIdentifier;
            $accessToken->expires_at = Carbon::createFromImmutable($accessTokenEntity->getExpiryDateTime());
            $accessToken->scopes = $this->scopeConverter->toDomainArray($accessTokenEntity->getScopes());
            $accessToken->revoked = false;
        });
    }
}
