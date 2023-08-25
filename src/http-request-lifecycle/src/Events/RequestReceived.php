<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\RequestLifeCycle\Events;

class_alias(\Hyperf\HttpServer\Event\RequestReceived::class, RequestReceived::class);

if (! class_exists(RequestReceived::class)) {
    /**
     * @deprecated v3.0, will be removed in v3.1, please use \Hyperf\HttpServer\Event\RequestReceived instead.
     */
    class RequestReceived extends \Hyperf\HttpServer\Event\RequestReceived
    {
    }
}
