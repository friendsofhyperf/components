<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Tracing;

use FriendsOfHyperf\Sentry\Switcher;
use Hyperf\Engine\Coroutine;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Throwable;

use function Hyperf\Tappable\tap;
use function Sentry\trace;

class Tracer
{
    public function __construct(protected Switcher $switcher)
    {
    }

    /**
     * Starts a new Transaction and returns it. This is the entry point to manual tracing instrumentation.
     */
    public function startTransaction(TransactionContext $transactionContext, array $customSamplingContext = []): Transaction
    {
        $hub = SentrySdk::setCurrentHub(
            tap(clone SentrySdk::getCurrentHub(), fn (HubInterface $hub) => $hub->pushScope())
        );

        $transactionContext->setData(['coroutine.id' => Coroutine::id()] + $transactionContext->getData());

        if ($transactionContext->getStartTimestamp() === null) {
            $transactionContext->setStartTimestamp(microtime(true));
        }

        if ($transactionContext->getStatus() === null) {
            $transactionContext->setStatus(SpanStatus::ok());
        }

        if ($transactionContext->getMetadata()->getSource() === null) {
            $transactionContext->setSource(TransactionSource::custom());
        }

        $transaction = $hub->startTransaction($transactionContext, $customSamplingContext);

        $hub->setSpan($transaction);

        return $transaction;
    }

    /**
     * Execute the given callable while wrapping it in a span added as a child to the current transaction and active span.
     * If there is no transaction active this is a no-op and the scope passed to the trace callable will be unused.
     *
     * @template T
     *
     * @param callable(Scope):T $trace
     * @return T
     */
    public function trace(callable $trace, SpanContext $context)
    {
        $isTracingExtraTagEnabled = $this->switcher->isTracingExtraTagEnabled('exception.stack_trace');

        if ($context->getStatus() === null) {
            $context->setStatus(SpanStatus::ok());
        }

        if ($context->getStartTimestamp() === null) {
            $context->setStartTimestamp(microtime(true));
        }

        $context->setData(['coroutine.id' => Coroutine::id()] + $context->getData());

        return trace(
            function (Scope $scope) use ($trace, $isTracingExtraTagEnabled) {
                try {
                    return $trace($scope);
                } catch (Throwable $exception) {
                    $span = $scope->getSpan();
                    if ($span !== null) {
                        $span->setStatus(SpanStatus::internalError())
                            ->setTags([
                                'error' => 'true',
                                'exception.class' => $exception::class,
                                'exception.code' => (string) $exception->getCode(),
                            ])
                            ->setData([
                                'exception.message' => $exception->getMessage(),
                            ]);
                        if ($isTracingExtraTagEnabled) {
                            $span->setData([
                                'exception.stack_trace' => (string) $exception,
                            ]);
                        }
                    }
                    throw $exception;
                }
            },
            $context
        );
    }
}
