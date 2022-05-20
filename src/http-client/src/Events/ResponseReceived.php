<?php

declare(strict_types=1);
/**
 * This file is part of http-client.
 *
 * @link     https://github.com/friendsofhyperf/http-client
 * @document https://github.com/friendsofhyperf/http-client/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Client\Events;

use FriendsOfHyperf\Http\Client\Request;
use FriendsOfHyperf\Http\Client\Response;

class ResponseReceived
{
    /**
     * The request instance.
     *
     * @var Request
     */
    public $request;

    /**
     * The response instance.
     *
     * @var Response
     */
    public $response;

    /**
     * Create a new event instance.
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
