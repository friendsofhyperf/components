<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Http\RequestLifeCycle\Events;

class_alias(\Hyperf\HttpServer\Event\RequestHandled::class, RequestHandled::class);

if (! class_exists(RequestHandled::class)) {
    /**
     * @deprecated v3.0, will be removed in v3.1, please use \Hyperf\HttpServer\Event\RequestHandled instead.
     */
    class RequestHandled extends \Hyperf\HttpServer\Event\RequestHandled
    {
    }
}
