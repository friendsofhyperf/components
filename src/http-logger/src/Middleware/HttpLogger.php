<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\HttpLogger\Middleware;

use FriendsOfHyperf\HttpLogger\Profile\LogProfile;
use FriendsOfHyperf\HttpLogger\Writer\LogWriter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HttpLogger implements MiddlewareInterface
{
    public function __construct(private LogProfile $logProfile, private LogWriter $logWriter)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($this->logProfile->shouldLogRequest($request)) {
            $this->logWriter->logRequest($request, $response);
        }

        return $response;
    }
}
