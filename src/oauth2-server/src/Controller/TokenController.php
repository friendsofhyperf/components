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

use FriendsOfHyperf\Oauth2\Server\Event\TokenRequestResolveEvent;
use Hyperf\Context\ResponseContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;

class TokenController
{
    public function __construct(
        private readonly AuthorizationServer $authorizationServer,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function index(RequestInterface $request): ResponseInterface
    {
        $response = ResponseContext::get();
        try {
            $response = $this->authorizationServer->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $e) {
            $response = $e->generateHttpResponse($response);
        }

        $event = $this->eventDispatcher->dispatch(
            new TokenRequestResolveEvent($response),
        );

        return $event->getResponse();
    }
}
