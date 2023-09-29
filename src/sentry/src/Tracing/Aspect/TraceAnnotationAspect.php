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
use FriendsOfHyperf\Sentry\Tracing\SpanContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Throwable;

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
        /** @var Trace|null $annotation */
        $annotation = $metadata->method[Trace::class] ?? null;

        if (! $annotation) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $data = [
            'coroutine.id' => Coroutine::id(),
            'arguments' => $arguments,
        ];

        $context = SpanContext::create(
            $annotation->op ?? 'method',
            $annotation->description ?? sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName)
        );

        try {
            $result = $proceedingJoinPoint->process();
            // $data['result'] = $result;
        } catch (Throwable $e) {
            if (! $this->switcher->isExceptionIgnored($e)) {
                $data = array_merge($data, [
                    'error' => true,
                    'exception.class' => get_class($e),
                    'exception.message' => $e->getMessage(),
                    'exception.code' => $e->getCode(),
                    'exception.stacktrace' => $e->getTraceAsString(),
                ]);
            }
            throw $e;
        } finally {
            $context->setData($data)->finish();
        }

        return $result;
    }
}
