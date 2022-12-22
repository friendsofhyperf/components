<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\MonologHook\Aspect;

use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Utils\Coroutine;
use Monolog\Handler\SyslogUdp\UdpSocket;

class UdpSocketAspect extends AbstractAspect
{
    public $classes = [
        UdpSocket::class . '::getSocket',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (Coroutine::inCoroutine()) {
            [$ip, $port] = (function () {
                return [$this->ip, $this->port];
            })->call($proceedingJoinPoint->getInstance());

            $key = sprintf('%s_%s_%s_%s', $proceedingJoinPoint->className, 'Socket', $ip, $port);
            return Context::getOrSet($key, function () use ($port) {
                $domain = AF_INET;
                $protocol = SOL_UDP;
                // Check if we are using unix sockets.
                if ($port === 0) {
                    $domain = AF_UNIX;
                    $protocol = IPPROTO_IP;
                }

                return socket_create($domain, SOCK_DGRAM, $protocol);
            });
        }

        return $proceedingJoinPoint->process();
    }
}
