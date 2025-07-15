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
use FriendsOfHyperf\Oauth2\Server\ValueObject\Scope;
use Hyperf\HttpServer\Contract\ResponseInterface as Response;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Symfony\Contracts\EventDispatcher\Event;

class AuthorizationRequestResolveEvent extends Event
{
    public const AUTHORIZATION_APPROVED = true;

    public const AUTHORIZATION_DENIED = false;

    private bool $authorizationResolution = self::AUTHORIZATION_DENIED;

    private ?Response $response = null;

    /**
     * @param Scope[] $scopes
     */
    public function __construct(
        private readonly AuthorizationRequestInterface $authorizationRequest,
        private array $scopes,
        private ClientInterface $client,
        private mixed $user
    ) {
    }

    public function getAuthorizationResolution(): bool
    {
        return $this->authorizationResolution;
    }

    public function resolveAuthorization(bool $authorizationResolution): self
    {
        $this->authorizationResolution = $authorizationResolution;
        $this->response = null;
        $this->stopPropagation();

        return $this;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;
        $this->stopPropagation();

        return $this;
    }

    public function getGrantTypeId(): string
    {
        return $this->authorizationRequest->getGrantTypeId();
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getUser(): mixed
    {
        return $this->user;
    }

    /**
     * @return Scope[]
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function isAuthorizationApproved(): bool
    {
        return $this->authorizationRequest->isAuthorizationApproved();
    }

    public function getRedirectUri(): ?string
    {
        return $this->authorizationRequest->getRedirectUri();
    }

    public function getState(): ?string
    {
        return $this->authorizationRequest->getState();
    }

    public function getCodeChallenge(): ?string
    {
        return $this->authorizationRequest->getCodeChallenge();
    }

    public function getCodeChallengeMethod(): ?string
    {
        return $this->authorizationRequest->getCodeChallengeMethod();
    }
}
