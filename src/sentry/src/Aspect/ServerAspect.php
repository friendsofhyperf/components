<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Aspect;

use FriendsOfHyperf\Sentry\SentryContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @property string|null $serverName
 */
class ServerAspect extends AbstractAspect
{
    public array $classes = [
        \Hyperf\HttpServer\Server::class . '::onRequest',
        \Hyperf\RpcServer\Server::class . '::onReceive',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        /** @var \Hyperf\HttpServer\Server|\Hyperf\RpcServer\Server $server */
        $server = $proceedingJoinPoint->getInstance();
        /** @var string|null $serverName */
        $serverName = (fn () => $this->serverName ?? null)->call($server);
        $serverName && SentryContext::setServerName($serverName);
        return $proceedingJoinPoint->process();
    }
}
