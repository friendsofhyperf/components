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
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\Tracing\SpanContext as SentrySpanContext;
use Sentry\Tracing\SpanStatus;
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

        if (! $annotation || ! $parentSpan = TraceContext::getRoot()) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments['keys'];
        $data = [
            'coroutine.id' => Coroutine::id(),
            'arguments' => $arguments,
        ];

        $anContext = new SentrySpanContext();
        $anContext->setOp($annotation->op ?? 'method');
        $anContext->setDescription($annotation->description ?? sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName));
        $anContext->setStartTimestamp(microtime(true));

        $anSpan = $parentSpan->startChild($anContext);

        // Set current span as root
        TraceContext::setRoot($anSpan);

        try {
            $result = $proceedingJoinPoint->process();
            // $data['result'] = $result;
            $anSpan->setStatus(SpanStatus::ok());
        } catch (Throwable $e) {
            $anSpan->setStatus(SpanStatus::internalError());
            if (! $this->switcher->isExceptionIgnored($e)) {
                $anSpan->setTags([
                    'exception.class' => get_class($e),
                    'exception.message' => $e->getMessage(),
                    'exception.code' => $e->getCode(),
                    'exception.stacktrace' => $e->getTraceAsString(),
                ]);
            }
            throw $e;
        } finally {
            $anContext->setData($data);
            $anContext->setEndTimestamp(microtime(true));
            $anSpan->finish(microtime(true));

            // Reset root span
            TraceContext::setRoot($parentSpan);
        }

        return $result;
    }
}
