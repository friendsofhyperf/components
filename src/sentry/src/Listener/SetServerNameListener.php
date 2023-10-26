<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Listener;

use FriendsOfHyperf\Sentry\SentryContext;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpServer\Event\RequestReceived;

class SetServerNameListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            RequestReceived::class,
        ];
    }

    /**
     * @param RequestReceived $event
     */
    public function process(object $event): void
    {
        SentryContext::setServerName($event->server);
    }
}
