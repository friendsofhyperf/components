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
use FriendsOfHyperf\Sentry\Tracing\TagManager;
use FriendsOfHyperf\Sentry\Tracing\TraceContext;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Coroutine as Co;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Sentry\SentrySdk;
use Sentry\Tracing\SpanContext as SentrySpanContext;
use Sentry\Tracing\SpanStatus;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Sentry\continueTrace;

class CoroutineAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Coroutine\Coroutine::create',
    ];

    public function __construct(
        protected Switcher $switcher,
        protected TagManager $tagManager
    ) {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if (! $this->switcher->isTracingEnable('coroutine')) {
            return $proceedingJoinPoint->process();
        }

        $callable = $proceedingJoinPoint->arguments['keys']['callable'];
        $parent = TraceContext::getSpan();

        $proceedingJoinPoint->arguments['keys']['callable'] = function () use ($callable, $parent) {
            $sentry = SentrySdk::init();
            $sentryTrace = $parent->toTraceparent();
            $baggage = $parent->toBaggage();

            $context = continueTrace($sentryTrace, $baggage);
            $context->setName('coroutine');
            $context->setOp('coroutine.create');
            $context->setDescription('#' . Coroutine::id());

            $transaction = $sentry->startTransaction($context);
            TraceContext::setTransaction($transaction);

            $coContext = new SentrySpanContext();
            $coContext->setOp('coroutine.execute');
            $coContext->setDescription('#' . Coroutine::id());
            $coContext->setStartTimestamp(microtime(true));
            $coSpan = $transaction->startChild($coContext);

            $sentry->setSpan($coSpan);
            TraceContext::setSpan($coSpan);

            defer(function () use ($sentry, $transaction, $coContext, $coSpan) {
                $coContext->setEndTimestamp(microtime(true));
                $coSpan->finish();
                $sentry->setSpan($transaction);
                $transaction->finish();
                // TraceContext::clearSpan();
                // TraceContext::clearTransaction();
            });

            $data = [];

            if ($this->tagManager->has('coroutine.id')) {
                $data[$this->tagManager->get('coroutine.id')] = Co::id();
            }

            try {
                $callable();
                $transaction->setStatus(SpanStatus::ok());
            } catch (Throwable $exception) {
                $transaction->setStatus(SpanStatus::internalError());
                $transaction->setTags([
                    'error' => true,
                    'exception.class' => $exception::class,
                    'exception.message' => $exception->getMessage(),
                    'exception.code' => $exception->getCode(),
                ]);
                if ($this->tagManager->has('coroutine.exception.stack_trace')) {
                    $data[$this->tagManager->get('coroutine.exception.stack_trace')] = (string) $exception;
                }

                throw $exception;
            } finally {
                $coContext->setData($data);
            }
        };

        return $proceedingJoinPoint->process();
    }
}
