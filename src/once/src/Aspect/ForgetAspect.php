<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Once\Aspect;

use FriendsOfHyperf\Once\Annotation\Forget;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Spatie\Once\Cache;

class ForgetAspect extends AbstractAspect
{
    public array $annotations = [
        Forget::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        Cache::getInstance()->forget($proceedingJoinPoint->getInstance());

        return $proceedingJoinPoint->process();
    }
}
