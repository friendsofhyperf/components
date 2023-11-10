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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext as SentrySpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

class TraceAnnotationAspect extends AbstractAspect
{
    public array $annotations = [
        Trace::class,
    ];

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        /** @var Trace|null $annotation */
        $annotation = $metadata->method[Trace::class] ?? null;

        if (! $annotation || ! $parent = TraceContext::getSpan()) {
            return $proceedingJoinPoint->process();
        }

        $data = [];
        if ($this->tagManager->has('annotation.coroutine.id')) {
            $data[$this->tagManager->get('annotation.coroutine.id')] = Coroutine::id();
        }
        if ($this->tagManager->has('annotation.arguments')) {
            $data[$this->tagManager->get('annotation.arguments')] = $proceedingJoinPoint->arguments['keys'];
        }

        $span = $parent->startChild(new SentrySpanContext());
        $span->setOp($annotation->op ?? 'method');
        $span->setDescription(
            $annotation->description ?? sprintf(
                '%s::%s()',
                $proceedingJoinPoint->className,
                $proceedingJoinPoint->methodName
            )
        );
        $span->setStartTimestamp(microtime(true));

        // Set current span as root
        SentrySdk::getCurrentHub()->setSpan($span);
        TraceContext::setSpan($span);

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->has('annotation.result')) {
                $data[$this->tagManager->get('annotation.result')] = $result;
            }
            $span->setStatus(SpanStatus::ok());
        } catch (Throwable $exception) {
            $span->setStatus(SpanStatus::internalError());
            $span->setTags([
                'error' => true,
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
            ]);
            if ($this->tagManager->has('annotation.exception.stack_trace')) {
                $data[$this->tagManager->get('annotation.exception.stack_trace')] = (string) $exception;
            }
            throw $exception;
        } finally {
            $span->setData($data);
            $span->finish(microtime(true));

            // Reset root span
            SentrySdk::getCurrentHub()->setSpan($parent);
            TraceContext::setSpan($parent);
        }

        return $result;
    }
}
