<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry;

use Hyperf\Context\ApplicationContext;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;
use Throwable;

use function Hyperf\Tappable\tap;
use function Sentry\trace as _trace;

/**
 * Starts a new Transaction and returns it. This is the entry point to manual tracing instrumentation.
 */
function startTransaction(TransactionContext $transactionContext, array $customSamplingContext = []): Transaction
{
    $hub = SentrySdk::setCurrentHub(
        tap(clone SentrySdk::getCurrentHub(), fn (HubInterface $hub) => $hub->pushScope())
    );

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
function trace(callable $trace, SpanContext $context)
{
    $container = ApplicationContext::getContainer();
    $isTracingExtraTagEnabled = false;

    if ($container->has(Switcher::class)) {
        $switcher = $container->get(Switcher::class);
        $isTracingExtraTagEnabled = $switcher instanceof Switcher && $switcher->isTracingExtraTagEnabled('exception.stack_trace');
    }

    if ($context->getStatus() === null) {
        $context->setStatus(SpanStatus::ok());
    }

    if ($context->getStartTimestamp() === null) {
        $context->setStartTimestamp(microtime(true));
    }

    return _trace(
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
