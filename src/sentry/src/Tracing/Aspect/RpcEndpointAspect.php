<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Constants;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\RpcMultiplex\Socket;

use function Hyperf\Tappable\tap;

class RpcEndpointAspect extends AbstractAspect
{
    public array $classes = [
        \Hyperf\RpcMultiplex\SocketFactory::class . '::get',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($socket) {
            if ($socket instanceof Socket) {
                Context::set(Constants::TRACE_RPC_SERVER_ADDRESS, $socket->getName());
                Context::set(Constants::TRACE_RPC_SERVER_PORT, $socket->getPort());
            }
        });
    }
}
