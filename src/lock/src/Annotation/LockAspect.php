<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/2.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Lock\Annotation;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
#[Aspect]
class LockAspect extends AbstractAspect
{
    public array $annotations = [
        Lock::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        return $proceedingJoinPoint->process();
    }
}
