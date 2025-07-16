<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Oauth2\Server\Middleware;

use Hyperf\Context\RequestContext;
use Hyperf\Context\ResponseContext;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ResourceServerMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResourceServer $server
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $validatedRequest = $this->server->validateAuthenticatedRequest($request);
            RequestContext::set($validatedRequest);
            return $handler->handle($validatedRequest);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse(ResponseContext::get());
        }
    }
}
