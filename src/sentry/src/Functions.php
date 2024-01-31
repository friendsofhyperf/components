<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use FriendsOfHyperf\Sentry\Constants;
use Hyperf\Context\ApplicationContext;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;

use function Hyperf\Tappable\tap;
use function Sentry\continueTrace as sentryContinueTrace;

/**
 * @return array{baggage: array<string, string>, sentry-trace: string, traceparent: string}
 */
function sentryCarrier(Span $span): array
{
    return [
        'sentry-trace' => $span->toTraceparent(),
        'baggage' => $span->toBaggage(),
        'traceparent' => $span->toW3CTraceparent(),
    ];
}

function startSpan(?string $op = null, ?string $description = null, bool $asParent = false): ?Span
{
    if (! $parent = SentrySdk::getCurrentHub()->getSpan()) {
        return null;
    }

    $span = $parent->startChild(new SpanContext());
    $span->setOp($op);
    $span->setDescription($description);
    $span->setStatus(SpanStatus::ok());
    $span->setStartTimestamp(microtime(true));

    if ($asParent) {
        SentrySdk::getCurrentHub()->setSpan($span);
    }

    return $span;
}

function startRequestTransaction(ServerRequestInterface $request, ...$options): Transaction
{
    // Get sentry-trace and baggage
    $sentryTrace = match (true) {
        $request->hasHeader('sentry-trace') => $request->getHeaderLine('sentry-trace'),
        $request->hasHeader('traceparent') => $request->getHeaderLine('traceparent'),
        default => '',
    };
    $baggage = $request->getHeaderLine('baggage');
    $container = ApplicationContext::getContainer();

    if ($container->has(RpcContext::class)) {
        $rpcContext = $container->get(RpcContext::class);
        $carrier = $rpcContext->get(Constants::RPC_CARRIER);
        if (! empty($carrier['sentry-trace']) && ! empty($carrier['baggage'])) {
            $sentryTrace = $carrier['sentry-trace'] ?? $carrier['traceparent'];
            $baggage = $carrier['baggage'];
        }
    }

    return continueTrace($sentryTrace, $baggage, ...$options);
}

function startCoroutineTransaction(Span $parent, ...$options): Transaction
{
    return continueTrace($parent->toTraceparent(), $parent->toBaggage(), ...$options);
}

function continueTrace(string $sentryTrace = '', string $baggage = '', ...$options): Transaction
{
    $hub = SentrySdk::setCurrentHub(
        tap(clone SentrySdk::getCurrentHub(), fn (HubInterface $hub) => $hub->pushScope())
    );

    $context = sentryContinueTrace($sentryTrace, $baggage);

    if (isset($options['name'])) {
        $context->setName($options['name']);
    }

    if (isset($options['op'])) {
        $context->setOp($options['op']);
    }

    if (isset($options['description'])) {
        $context->setDescription($options['description']);
    }

    if (isset($options['source']) && $options['source'] instanceof TransactionSource) {
        $context->setSource($options['source']);
    } else {
        $context->setSource(TransactionSource::custom());
    }

    $transaction = $hub->startTransaction($context);
    $transaction->setStartTimestamp(microtime(true));
    $transaction->setStatus(SpanStatus::ok());

    $hub->setSpan($transaction);

    return $transaction;
}