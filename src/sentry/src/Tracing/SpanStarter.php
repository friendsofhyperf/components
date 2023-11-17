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
use Hyperf\Context\ApplicationContext;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionSource;

use function Hyperf\Tappable\tap;
use function Sentry\continueTrace;

trait SpanStarter
{
    protected function startSpan(?string $op = null, ?string $description = null, bool $asParent = false): ?Span
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

    protected function startRequestTransaction(ServerRequestInterface $request, ...$options): Transaction
    {
        // Get sentry-trace and baggage
        $sentryTrace = $request->getHeaderLine('sentry-trace', '');
        $baggage = $request->getHeaderLine('baggage', '');
        $container = $this->container ?? ApplicationContext::getContainer();

        if ($container->has(RpcContext::class)) {
            $rpcContext = $container->get(RpcContext::class);
            $carrier = $rpcContext->get(Constants::RPC_CARRIER);
            if (! empty($carrier['sentry-trace']) && ! empty($carrier['baggage'])) {
                $sentryTrace = $carrier['sentry-trace'];
                $baggage = $carrier['baggage'];
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
        $sentry = SentrySdk::setCurrentHub(
            tap(clone SentrySdk::getCurrentHub(), fn ($sentry) => $sentry->pushScope())
        );

        $context = continueTrace($sentryTrace, $baggage);
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

        $transaction = $sentry->startTransaction($context);
        $transaction->setStartTimestamp(microtime(true));
        $transaction->setStatus(SpanStatus::ok());

        $sentry->setSpan($transaction);
        TraceContext::setTransaction($transaction);

        return $transaction;
    }
}
