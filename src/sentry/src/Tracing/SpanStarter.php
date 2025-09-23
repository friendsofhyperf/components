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

use FriendsOfHyperf\Sentry\Constants;
use FriendsOfHyperf\Sentry\Switcher;
use FriendsOfHyperf\Sentry\Util\Carrier;
use Hyperf\Context\ApplicationContext;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\State\Scope;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;
use Throwable;

use function Hyperf\Tappable\tap;
use function Sentry\continueTrace;
use function Sentry\trace;

trait SpanStarter
{
    /**
     * Execute the given callable while wrapping it in a span added as a child to the current transaction and active span.
     * If there is no transaction active this is a no-op and the scope passed to the trace callable will be unused.
     *
     * @template T
     *
     * @param callable(Scope):T $trace
     * @return T
     */
    protected function trace(callable $trace, SpanContext $context)
    {
        static $isTracingExtraTagEnabled = null;

        if ($isTracingExtraTagEnabled !== null) {
            $isTracingExtraTagEnabled = isset($this->switcher)
            && $this->switcher instanceof Switcher
            && $this->switcher->isTracingExtraTagEnabled('exception.stack_trace');
        }

        if ($context->getStatus() === null) {
            $context->setStatus(SpanStatus::ok());
        }

        if ($context->getStartTimestamp() === null) {
            $context->setStartTimestamp(microtime(true));
        }

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

    /**
     * @deprecated since v3.1, will be removed in v3.2, use trace() instead.
     */
    protected function startSpan(
        ?string $op = null,
        ?string $description = null,
        ?string $origin = null,
        bool $asParent = false
    ): ?Span {
        if (! $parent = SentrySdk::getCurrentHub()->getSpan()) {
            return null;
        }

        if ($parent instanceof Transaction && ! $parent->getSampled()) {
            return null;
        }

        $spanContext = SpanContext::make()->setOp($op)
            ->setDescription($description)
            ->setOrigin($origin)
            ->setStatus(SpanStatus::ok())
            ->setStartTimestamp(microtime(true));

        return tap(
            $parent->startChild($spanContext),
            fn (Span $span) => $asParent && SentrySdk::getCurrentHub()->setSpan($span)
        );
    }

    protected function startRequestTransaction(ServerRequestInterface $request, ...$options): Transaction
    {
        // Get sentry-trace and baggage
        $sentryTrace = match (true) {
            $request->hasHeader('sentry-trace') => $request->getHeaderLine('sentry-trace'),
            $request->hasHeader('traceparent') => $request->getHeaderLine('traceparent'),
            default => '',
        };
        $baggage = $request->getHeaderLine('baggage');
        $container = $this->container ?? ApplicationContext::getContainer();

        // Rpc Context
        if ($container->has(RpcContext::class)) {
            $rpcContext = $container->get(RpcContext::class);
            /** @var null|string $payload */
            $payload = $rpcContext->get(Constants::TRACE_CARRIER);
            if ($payload) {
                $carrier = Carrier::fromJson($payload);
                [$sentryTrace, $baggage] = [$carrier->getSentryTrace(), $carrier->getBaggage()];
            }
        }

        return $this->continueTrace($sentryTrace, $baggage, ...$options);
    }

    protected function startCoroutineTransaction(Span $parent, ...$options): Transaction
    {
        return $this->continueTrace($parent->toTraceparent(), $parent->toBaggage(), ...$options);
    }

    protected function continueTrace(string $sentryTrace = '', string $baggage = '', ...$options): Transaction
    {
        $hub = SentrySdk::setCurrentHub(
            tap(clone SentrySdk::getCurrentHub(), fn (HubInterface $hub) => $hub->pushScope())
        );

        // Build transaction context
        $transactionContext = continueTrace($sentryTrace, $baggage)
            ->setStartTimestamp(microtime(true))
            ->setStatus(SpanStatus::ok());

        // Set additional options
        array_walk($options, function ($value, $key) use ($transactionContext) {
            match ($key) {
                'name' => is_string($value) && $transactionContext->setName($value),
                'op' => is_string($value) && $transactionContext->setOp($value),
                'description' => is_string($value) && $transactionContext->setDescription($value),
                'origin' => is_string($value) && $transactionContext->setOrigin($value),
                'source' => $transactionContext->setSource(
                    $value instanceof TransactionSource ? $value : TransactionSource::custom()
                ),
                default => null,
            };
        });

        return tap(
            $hub->startTransaction($transactionContext),
            fn ($transaction) => $hub->setSpan($transaction)
        );
    }
}
