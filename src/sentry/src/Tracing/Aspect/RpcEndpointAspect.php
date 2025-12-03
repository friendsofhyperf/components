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

use FriendsOfHyperf\Sentry\Feature;
use FriendsOfHyperf\Sentry\SentryContext;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

use function Hyperf\Tappable\tap;

class RpcEndpointAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\RpcMultiplex\SocketFactory::get',
        'Hyperf\JsonRpc\JsonRpcHttpTransporter::getNode',
        'Hyperf\JsonRpc\JsonRpcPoolTransporter::getConnection',
    ];

    public function __construct(protected Feature $feature)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) {
            if (! $this->feature->isTracingSpanEnabled('rpc')) {
                return;
            }

            // RpcMultiplex
            if ($result instanceof \Hyperf\RpcMultiplex\Socket) {
                SentryContext::setRpcServerAddress($result->getName());
                SentryContext::setRpcServerPort($result->getPort());
            }
            // JsonRpcHttpTransporter
            if ($result instanceof \Hyperf\LoadBalancer\Node) {
                SentryContext::setRpcServerAddress($result->host);
                SentryContext::setRpcServerPort($result->port);
            }
            // JsonRpcPoolTransporter
            if ($result instanceof \Hyperf\JsonRpc\Pool\RpcConnection) {
                /** @var null|\Hyperf\Engine\Contract\SocketInterface $socket */
                $socket = (fn () => $this->connection ?? null)->call($result);
                if (method_exists($socket, 'getSocketOption')) {
                    /** @var null|\Hyperf\Engine\Contract\Socket\SocketOptionInterface $option */
                    $option = $socket->getSocketOption();
                    if ($option instanceof \Hyperf\Engine\Contract\Socket\SocketOptionInterface) {
                        SentryContext::setRpcServerAddress($option->getHost());
                        SentryContext::setRpcServerPort($option->getPort());
                    }
                }
            }
        });
    }
}
