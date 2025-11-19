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

use function Hyperf\Tappable\tap;

class RpcEndpointAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\RpcMultiplex\SocketFactory::get',
        'Hyperf\JsonRpc\JsonRpcHttpTransporter::getNode',
        'Hyperf\JsonRpc\JsonRpcPoolTransporter::getConnection',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return tap($proceedingJoinPoint->process(), function ($result) {
            // RpcMultiplex
            if ($result instanceof \Hyperf\RpcMultiplex\Socket) {
                Context::set(Constants::TRACE_RPC_SERVER_ADDRESS, $result->getName());
                Context::set(Constants::TRACE_RPC_SERVER_PORT, $result->getPort());
            }
            // JsonRpcHttpTransporter
            if ($result instanceof \Hyperf\LoadBalancer\Node) {
                Context::set(Constants::TRACE_RPC_SERVER_ADDRESS, $result->host);
                Context::set(Constants::TRACE_RPC_SERVER_PORT, $result->port);
            }
            // JsonRpcPoolTransporter
            if ($result instanceof \Hyperf\JsonRpc\Pool\RpcConnection) {
                /** @var null|\Hyperf\Engine\Contract\SocketInterface $socket */
                $socket = (fn () => $this->connection ?? null)->call($result);
                if (method_exists($socket, 'getSocketOption')) {
                    /** @var null|\Hyperf\Engine\Contract\Socket\SocketOptionInterface $option */
                    $option = $socket->getSocketOption();
                    if ($option instanceof \Hyperf\Engine\Contract\Socket\SocketOptionInterface) {
                        Context::set(Constants::TRACE_RPC_SERVER_ADDRESS, $option->getHost());
                        Context::set(Constants::TRACE_RPC_SERVER_PORT, $option->getPort());
                    }
                }
            }
        });
    }
}
