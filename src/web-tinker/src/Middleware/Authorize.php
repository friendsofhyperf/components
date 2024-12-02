<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker\Middleware;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Authorize implements MiddlewareInterface
{
    public function __construct(
        protected ConfigInterface $config,
        protected HttpResponse $response
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->allowedToUseTinker()
            ? $handler->handle($request)
            : $this->response->html('')->withStatus(403);
    }

    protected function allowedToUseTinker(): bool
    {
        if (! $this->config->get('web-tinker.enabled', false)) {
            return false;
        }

        return true;
    }
}
