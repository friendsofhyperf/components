<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\MonologHook\Aspect;

use Hyperf\Context\Context;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\Handler\SyslogUdp\UdpSocket;

use function Hyperf\Coroutine\defer;

/**
 * @property string $ip
 * @property int $port
 */
class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::getSocket',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (Coroutine::inCoroutine()) {
            [$ip, $port] = (fn () => [$this->ip, $this->port])->call($proceedingJoinPoint->getInstance());

            $key = sprintf(
                '%s::%s@%s:%s',
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName,
                $ip,
                $port
            );

            return Context::getOrSet($key, function () use ($port) {
                $domain = AF_INET;
                $protocol = SOL_UDP;

                if ($port === 0) { // Check if we are using unix sockets.
                    $domain = AF_UNIX;
                    $protocol = IPPROTO_IP;
                }

                $socket = socket_create($domain, SOCK_DGRAM, $protocol);

                defer(fn () => socket_close($socket));

                return $socket;
            });
        }

        return $proceedingJoinPoint->process();
    }
}
