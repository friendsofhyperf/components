<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Controller;

use FriendsOfHyperf\Oauth2\Server\Event\AuthorizationRequestResolveEventFactory;
use FriendsOfHyperf\Oauth2\Server\Manager\ClientManagerInterface;
use Hyperf\Context\ResponseContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\EventDispatcher\EventDispatcherInterface;

final class AuthorizationController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AuthorizationServer $authorizationServer,
        private readonly AuthorizationRequestResolveEventFactory $eventFactory,
        private readonly ClientManagerInterface $clientManager,
    ) {
    }

    public function index(RequestInterface $request)
    {
        $response = ResponseContext::get();
        try {
            $authRequest = $this->authorizationServer->validateAuthorizationRequest($request);
            if ($authRequest->getCodeChallengeMethod() === 'plain') {
                $client = $this->clientManager->find($authRequest->getClient()->getIdentifier());
                if (! $client->isPlainTextPkceAllowed()) {
                    throw OAuthServerException::invalidRequest('code_challenge_method', 'Plain code challenge method is not allowed for this client');
                }
            }

            $event = $this->eventDispatcher->dispatch(
                $this->eventFactory->fromAuthorizationRequest($authRequest)
            );
            if ($eventResponse = $event->getResponse()) {
                return $eventResponse;
            }
            $authRequest->setAuthorizationApproved($event->getAuthorizationResolution());

            return $this->authorizationServer->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $e) {
            return $e->generateHttpResponse($response);
        }
    }
}
