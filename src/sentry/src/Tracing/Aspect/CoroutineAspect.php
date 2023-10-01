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
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Engine\Coroutine as Co;
use Sentry\Tracing\SpanContext as SentrySpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function Hyperf\Coroutine\defer;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function __construct(protected Switcher $switcher)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('coroutine') || ! $parentSpan = TraceContext::getRoot()) {
            return $proceedingJoinPoint->process();
        }

        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $waitGroup = TraceContext::getWaitGroup();
        $waitGroup->add();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $parentSpan, $waitGroup) {
            $coContext = new SentrySpanContext();
            $coContext->setOp('coroutine.run');
            $coContext->setDescription('#' . Coroutine::id());
            $coContext->setStartTimestamp(microtime(true));
            // Pre-activate the context to make sure that the current context is available
            $coContext->setEndTimestamp(microtime(true));

            $coSpan = $parentSpan->startChild($coContext);
            // Pre-activate the span to make sure that the current span is available
            $coSpan->finish();

            // Set current span as root span
            TraceContext::setRoot($coSpan);

            defer(function () use ($coContext, $coSpan, $waitGroup) {
                $coContext->setEndTimestamp(microtime(true));
                $coSpan->finish();
                TraceContext::clearRoot();
                $waitGroup->done();
            });

            $data = [
                'coroutine.id' => Co::id(),
            ];

            try {
                $callable();
                $coSpan->setStatus(SpanStatus::ok());
            } catch (Throwable $e) {
                $coSpan->setStatus(SpanStatus::internalError());
                if (! $this->switcher->isExceptionIgnored($e)) {
                    $data = array_merge($data, [
                        'exception.class' => get_class($e),
                        'exception.message' => $e->getMessage(),
                        'exception.code' => $e->getCode(),
                        'exception.stacktrace' => $e->getTraceAsString(),
                    ]);
                }
                throw $e;
            } finally {
                $coContext->setData($data);
            }
        };

        return $proceedingJoinPoint->process();
    }
}
