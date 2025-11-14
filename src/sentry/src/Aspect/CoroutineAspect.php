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

use FriendsOfHyperf\Sentry\Integration;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;
use Sentry\SentrySdk;
use Throwable;

class CoroutineAspect extends AbstractAspect
{
    public const CONTEXT_KEYS = [
        \Psr\Http\Message\ServerRequestInterface::class,
    ];

    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
        'Hyperf\Coroutine\Coroutine::printLog',
    ];

    public function __construct()
    {
        $this->priority = PHP_INT_MAX - 1;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        match ($proceedingJoinPoint->methodName) {
            'create' => $this->handleCreate($proceedingJoinPoint),
            'printLog' => $this->handlePrintLog($proceedingJoinPoint),
            default => null,
        };

        return $proceedingJoinPoint->process();
    }

    protected function handleCreate(ProceedingJoinPoint $proceedingJoinPoint): void
    {
        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $cid = Co::id();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $cid) {
            // Restore the Context in the new Coroutine.
            foreach (self::CONTEXT_KEYS as $key) {
                Context::getOrSet($key, fn () => Context::get($key, coroutineId: $cid));
            }

            // Defer the flushing of events until the coroutine completes.
            Co::defer(fn () => Integration::flushEvents());

            // Continue the callable in the new Coroutine.
            $callable();
        };
    }

    protected function handlePrintLog(ProceedingJoinPoint $proceedingJoinPoint): void
    {
        $throwable = $proceedingJoinPoint->arguments['keys']['throwable'] ?? null;

        if (! $throwable instanceof Throwable) {
            return;
        }

        Co::defer(fn () => SentrySdk::getCurrentHub()->captureException($throwable));
    }
}
