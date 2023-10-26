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

        $anContext = new SentrySpanContext();
        $anContext->setOp($annotation->op ?? 'method');
        $anContext->setDescription($annotation->description ?? sprintf('%s::%s()', $proceedingJoinPoint->className, $proceedingJoinPoint->methodName));
        $anContext->setStartTimestamp(microtime(true));

        $anSpan = $parent->startChild($anContext);

        // Set current span as root
        TraceContext::setSpan($anSpan);

        try {
            $result = $proceedingJoinPoint->process();
            if ($this->tagManager->has('annotation.result')) {
                $data[$this->tagManager->get('annotation.result')] = $result;
            }
            $anSpan->setStatus(SpanStatus::ok());
        } catch (Throwable $exception) {
            $anSpan->setStatus(SpanStatus::internalError());
            $anSpan->setTags([
                'exception.class' => $exception::class,
                'exception.message' => $exception->getMessage(),
                'exception.code' => $exception->getCode(),
                'exception.stacktrace' => (string) $exception,
            ]);
            throw $exception;
        } finally {
            $anContext->setData($data);
            $anContext->setEndTimestamp(microtime(true));
            SentrySdk::getCurrentHub()->setSpan($anSpan);
            $anSpan->finish(microtime(true));
            SentrySdk::getCurrentHub()->setSpan($parent);

            // Reset root span
            TraceContext::setSpan($parent);
        }

        return $result;
    }
}
