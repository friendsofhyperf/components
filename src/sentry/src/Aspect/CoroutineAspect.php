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

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    protected array $keys = [
        \Sentry\SentrySdk::class,
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $keys = $this->keys;

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $keys) {
            Context::copy(Co::pid(), $keys);
            $callable();
        };

        return $proceedingJoinPoint->process();
    }
}
