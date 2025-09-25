<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing\Aspect;

use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Tracing\Annotation\Trace;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;

use function FriendsOfHyperf\Sentry\trace;
use function Hyperf\Tappable\tap;

class TraceAnnotationAspect extends AbstractAspect
{
    public array $annotations = [
        Trace::class,
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var null|Trace $annotation */
        $annotation = $metadata->method[Trace::class] ?? null;

        if (! $annotation) {
            return $proceedingJoinPoint->process();
        }

        $data = ['coroutine.id' => Coroutine::id()];
        $methodName = $proceedingJoinPoint->methodName;

        if (in_array($methodName, ['__call', '__callStatic'])) {
            $methodName = $proceedingJoinPoint->arguments['keys']['name'] ?? $proceedingJoinPoint->methodName;
            $data['annotation.arguments'] = $proceedingJoinPoint->arguments['keys']['arguments'] ?? $proceedingJoinPoint->arguments['keys'];
        } else {
            $data['annotation.arguments'] = $proceedingJoinPoint->arguments['keys'];
        }

        return trace(
            function (Scope $scope) use ($proceedingJoinPoint) {
                return tap($proceedingJoinPoint->process(), function ($result) use ($scope) {
                    if ($this->switcher->isTracingExtraTagEnabled('annotation.result')) {
                        $scope->getSpan()?->setData(['annotation.result' => $result]);
                    }
                });
            },
            SpanContext::make()
                ->setOp($annotation->op ?? 'method')
                ->setDescription($annotation->description ?? sprintf(
                    '%s::%s()',
                    $proceedingJoinPoint->className,
                    $methodName
                ))
                ->setData($data)
        );
    }
}
