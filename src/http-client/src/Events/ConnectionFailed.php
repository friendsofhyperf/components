<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Http\Client\Events;

use FriendsOfHyperf\Http\Client\Request;

class ConnectionFailed
{
    /**
     * The request instance.
     *
     * @var Request
     */
    public $request;

    /**
     * Create a new event instance.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
}
