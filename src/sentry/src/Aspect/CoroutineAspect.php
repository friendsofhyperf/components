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

use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;
use Sentry\SentrySdk;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable) {
            Context::copy(Co::pid(), [SentrySdk::class]);
            $callable();
        };

        return $proceedingJoinPoint->process();
    }
}
