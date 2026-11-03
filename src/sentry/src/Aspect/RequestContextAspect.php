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

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;

use function Hyperf\Coroutine\defer;

/**
 * Request coroutines are created by the server, not by Hyperf\Coroutine\Coroutine::create,
 * so CoroutineAspect does not cover them and they would all share the global hub.
 */
class RequestContextAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\HttpServer\Server::onRequest',
        'Hyperf\RpcServer\Server::onReceive',
    ];

    public function __construct()
    {
        $this->priority = PHP_INT_MAX;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        SentrySdk::startContext();

        // Deferred before the request runs, so it ends after the deferred transaction finish and RequestTerminated.
        defer(fn () => SentrySdk::endContext());

        return $proceedingJoinPoint->process();
    }
}
