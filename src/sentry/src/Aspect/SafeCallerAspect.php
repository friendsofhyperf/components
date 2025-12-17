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

use FriendsOfHyperf\Sentry\Annotation\SafeCaller as SafeCallerAnnotation;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Throwable;

use function Hyperf\Support\value;

class SafeCallerAspect extends AbstractAspect
{
    public array $annotations = [
        SafeCallerAnnotation::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint): mixed
    {
        // 获取注解实例
        $annotation = $proceedingJoinPoint->getAnnotationMetadata();

        /** @var SafeCallerAnnotation $safeCaller */
        $safeCaller = $annotation->method[SafeCallerAnnotation::class];

        try {
            return $proceedingJoinPoint->process();
        } catch (Throwable $e) {
            $report = true;

            if (is_callable($safeCaller->exceptionHandler)) {
                $report = call_user_func($safeCaller->exceptionHandler, $e);
            }

            $report && SentrySdk::getCurrentHub()->captureException($e);

            return value($safeCaller->default, $e);
        }
    }
}
