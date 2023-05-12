<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\SuperGlobalsPatch\Aspect;

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\SuperGlobals\Listener\SuperGlobalsInitializeListener;

class SuperGlobalsInitializeListenerAspect extends AbstractAspect
{
    public array $classes = [
        SuperGlobalsInitializeListener::class . '::listen',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $events = $proceedingJoinPoint->process();
        $events[] = RequestReceived::class;

        return $events;
    }
}
