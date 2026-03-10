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

use Closure;
use Hyperf\Context\Context;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\State\RuntimeContextManager;

use function Hyperf\Support\make;

/**
 * @mixin \Sentry\SentrySdk
 */
class SentrySdkAspect extends AbstractAspect
{
    public array $classes = [
        SentrySdk::class . '::init',
        SentrySdk::class . '::setCurrentHub',
        SentrySdk::class . '::getRuntimeContextManager',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return match ($proceedingJoinPoint->methodName) {
            'init' => $this->handleInit($proceedingJoinPoint),
            'setCurrentHub' => $this->handleSetCurrentHub($proceedingJoinPoint),
            'getRuntimeContextManager' => $this->handleGetRuntimeContextManager($proceedingJoinPoint),
            default => $proceedingJoinPoint->process(),
        };
    }

    private function handleInit(ProceedingJoinPoint $proceedingJoinPoint)
    {
        Context::set(
            RuntimeContextManager::class,
            new RuntimeContextManager(make(HubInterface::class))
        );

        return SentrySdk::getCurrentHub();
    }

    private function handleSetCurrentHub(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $arguments = $proceedingJoinPoint->arguments['keys'] ?? [];
        $hub = $arguments['hub'];
        // @phpstan-ignore-next-line
        Closure::bind(fn () => static::getRuntimeContextManager()->setCurrentHub($hub), null, SentrySdk::class)();

        return $hub;
    }

    private function handleGetRuntimeContextManager(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return Context::getOrSet(
            RuntimeContextManager::class,
            fn () => new RuntimeContextManager(make(HubInterface::class))
        );
    }
}
