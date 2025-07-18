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
use FriendsOfHyperf\Oauth2\Server\Entity\AuthCode;
use FriendsOfHyperf\Oauth2\Server\Manager\AuthorizationCodeManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use FriendsOfHyperf\Oauth2\Server\Model\AuthorizationCode;
use FriendsOfHyperf\Oauth2\Server\Model\ClientInterface;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

use function Hyperf\Tappable\tap;

final class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    public function __construct(
        private readonly AuthorizationCodeManagerInterface $authorizationCodeManager,
        private readonly ClientManagerInterface $clientManager,
        private readonly ScopeConverterInterface $scopeConverter
    ) {
    }

    public function getNewAuthCode(): AuthCode
    {
        return new AuthCode();
    }

    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $authorizationCode = $this->authorizationCodeManager->find($authCodeEntity->getIdentifier());

        if ($authorizationCode !== null) {
            throw UniqueTokenIdentifierConstraintViolationException::create();
        }

        $authorizationCode = $this->buildAuthorizationCode($authCodeEntity);

        $this->authorizationCodeManager->save($authorizationCode);
    }

    public function revokeAuthCode(string $codeId): void
    {
        $authorizationCode = $this->authorizationCodeManager->find($codeId);

        if ($authorizationCode === null) {
            return;
        }

        $authorizationCode->revoke();

        $this->authorizationCodeManager->save($authorizationCode);
    }

    public function isAuthCodeRevoked(string $codeId): bool
    {
        $authorizationCode = $this->authorizationCodeManager->find($codeId);

        if ($authorizationCode === null) {
            return true;
        }

        return $authorizationCode->isRevoked();
    }

    private function buildAuthorizationCode(AuthCodeEntityInterface $authCodeEntity): AuthorizationCode
    {
        /** @var ClientInterface $client */
        $client = $this->clientManager->find($authCodeEntity->getClient()->getIdentifier());
        return tap(new AuthorizationCode(), function (AuthorizationCode $authorizationCode) use ($authCodeEntity, $client) {
            $authorizationCode->user_id = $authCodeEntity->getUserIdentifier();
            $authorizationCode->client_id = $client->getIdentifier();
            $authorizationCode->code = $authCodeEntity->getIdentifier();
            $authorizationCode->expires_at = Carbon::createFromImmutable($authCodeEntity->getExpiryDateTime());
            $authorizationCode->scopes = $this->scopeConverter->toDomainArray($authCodeEntity->getScopes());
        });
    }
}
