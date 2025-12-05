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

use Hyperf\Engine\Coroutine;
use Sentry\SentrySdk;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Sentry\Tracing\TransactionSource;
use Throwable;

use function Hyperf\Coroutine\defer;
use function Sentry\trace;

class Tracer
{
    /**
     * Starts a new Transaction and returns it. This is the entry point to manual tracing instrumentation.
     */
    public function startTransaction(TransactionContext $transactionContext, array $customSamplingContext = []): Transaction
    {
        $hub = SentrySdk::getCurrentHub();
        $hub->pushScope();
        $hub->configureScope(static fn (Scope $scope) => $scope->clearBreadcrumbs());

        defer(static fn () => $hub->popScope());

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
     * @param (callable(Scope):T) $trace
     * @return T
     */
    public function trace(callable $trace, SpanContext $context)
    {
        if ($context->getStatus() === null) {
            $context->setStatus(SpanStatus::ok());
        }

        if ($context->getStartTimestamp() === null) {
            $context->setStartTimestamp(microtime(true));
        }

        $context->setData(['coroutine.id' => Coroutine::id()] + $context->getData());

        return trace(
            function (Scope $scope) use ($trace) {
                try {
                    return $trace($scope);
                } catch (Throwable $exception) {
                    $scope->getSpan()?->setStatus(SpanStatus::internalError());
                    throw $exception;
                }
            },
            $context
        );
    }
}
