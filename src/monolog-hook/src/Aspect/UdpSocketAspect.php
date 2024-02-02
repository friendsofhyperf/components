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

use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Monolog\Handler\SyslogUdp\UdpSocket;
use Socket;

/**
 * @property Socket|null $socket
 */
class UdpSocketAspect extends AbstractAspect
{
    public array $classes = [
        UdpSocket::class . '::getSocket',
    ];

    public static ?Socket $coSocket = null;

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! Coroutine::inCoroutine()) {
            return $proceedingJoinPoint->process();
        }

        if (self::$coSocket instanceof Socket) {
            return self::$coSocket;
        }

        $instance = $proceedingJoinPoint->getInstance();
        $nonCoSocket = (fn () => $this->socket)->call($instance); // Save the socket of non-coroutine.
        (fn () => $this->socket = null)->call($instance); // Unset the socket of non-coroutine.

        self::$coSocket = $proceedingJoinPoint->process(); // ReCreate the socket in coroutine.
        (fn () => $this->socket = $nonCoSocket)->call($instance); // Restore the socket of non-coroutine.

        return self::$coSocket;
    }
}
