<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/http-request-lifecycle.
 *
 * @link     https://github.com/friendsofhyperf/http-request-lifecycle
 * @document https://github.com/friendsofhyperf/http-request-lifecycle/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\HttpRequestLifeCycle\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractEvent
{
    /**
     * @var null|ServerRequestInterface
     */
    public $request;

    /**
     * @var null|ResponseInterface
     */
    public $response;

    public function __construct($request, $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
