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
use Hyperf\Engine\Coroutine as Co;
use Sentry\SentrySdk;
use Throwable;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    protected array $keys = [
        SentrySdk::class,
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $keys = $this->keys;
        $cid = Co::id();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $cid, $keys) {
            $from = Co::getContextFor($cid);
            $current = Co::getContextFor();

            foreach ($keys as $key) {
                if (isset($from[$key])) {
                    $current[$key] = $from[$key];
                }
            }

            try {
                $callable();
            } catch (Throwable $throwable) {
                SentrySdk::getCurrentHub()->captureException($throwable);
                throw $throwable;
            }
        };

        return $proceedingJoinPoint->process();
    }
}
