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
use Sentry\SentrySdk;

class CrontabExecutorAspect extends AbstractAspect
{
    public array $classes = [
        \Hyperf\Crontab\Strategy\Executor::class . '::catchToExecute',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! class_exists(\Hyperf\Crontab\Event\BeforeExecute::class)) {
            SentrySdk::init();
        }

        return $proceedingJoinPoint->process();
    }
}
