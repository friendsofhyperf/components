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
use FriendsOfHyperf\Oauth2\Server\Entity\RefreshToken as RefreshTokenEntity;
use FriendsOfHyperf\Oauth2\Server\Manager\AccessTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\RefreshTokenManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\RefreshToken as RefreshTokenModel;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

use function Hyperf\Tappable\tap;

final class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private readonly RefreshTokenManagerInterface $refreshTokenManager,
        private readonly AccessTokenManagerInterface $accessTokenManager
    ) {
    }

    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $refreshToken = $this->refreshTokenManager->find($refreshTokenEntity->getIdentifier());

        if ($refreshToken !== null) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $refreshToken = $this->buildRefreshTokenModel($refreshTokenEntity);

        $this->refreshTokenManager->save($refreshToken);
    }

    /**
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId): void
    {
        $refreshToken = $this->refreshTokenManager->find($tokenId);

        if ($refreshToken === null) {
            return;
        }

        $refreshToken->revoke();

        $this->refreshTokenManager->save($refreshToken);
    }

    /**
     * @param string $tokenId
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshToken = $this->refreshTokenManager->find($tokenId);

        if ($refreshToken === null) {
            return true;
        }

        return $refreshToken->isRevoked();
    }

    private function buildRefreshTokenModel(RefreshTokenEntityInterface $refreshTokenEntity): RefreshTokenModel
    {
        $accessToken = $this->accessTokenManager->find($refreshTokenEntity->getAccessToken()->getIdentifier());
        return tap(new RefreshTokenModel(), function (RefreshTokenModel $model) use ($accessToken, $refreshTokenEntity) {
            $model->id = $refreshTokenEntity->getIdentifier();
            $model->expires_at = Carbon::createFromImmutable($refreshTokenEntity->getExpiryDateTime());
            $model->access_token_id = $accessToken->getIdentifier();
        });
    }
}
