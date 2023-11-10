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

use Hyperf\Context\ApplicationContext;
use Hyperf\Rpc\Context as RpcContext;
use Psr\Http\Message\ServerRequestInterface;
use Sentry\SentrySdk;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\Transaction;

use function Sentry\continueTrace;

trait SpanStarter
{
    protected function startSpan(?string $op = null, ?string $description = null): ?Span
    {
        if (! $parent = SentrySdk::getCurrentHub()->getSpan()) {
            return null;
        }

        $span = $parent->startChild(new SpanContext());
        $span->setOp($op);
        $span->setDescription($description);
        $span->setStatus(SpanStatus::ok());
        $span->setStartTimestamp(microtime(true));

        SentrySdk::getCurrentHub()->setSpan($span);

        return $span;
    }

    protected function startRequestTransaction(ServerRequestInterface $request, string $name, string $op, ?string $description = null): Transaction
    {
        // Get sentry-trace and baggage
        $sentryTrace = $request->getHeaderLine('sentry-trace', '');
        $baggage = $request->getHeaderLine('baggage', '');
        $container = $this->container ?? ApplicationContext::getContainer();

        if ($container->has(RpcContext::class)) {
            $rpcContext = $container->get(RpcContext::class);
            $carrier = $rpcContext->get(TraceContext::RPC_CARRIER);
            if (! empty($carrier['sentry-trace']) && ! empty($carrier['baggage'])) {
                $sentryTrace = $carrier['sentry-trace'];
                $baggage = $carrier['baggage'];
            }
        }

        return $this->continueTrace(
            $name,
            $op,
            $description,
            $sentryTrace,
            $baggage
        );
    }

    protected function startCoroutineTransaction(Span $parent, string $name, string $op, ?string $description = null): Transaction
    {
        return $this->continueTrace(
            $name,
            $op,
            $description,
            $parent->toTraceparent(),
            $parent->toBaggage()
        );
    }

    protected function continueTrace(string $name, string $op, ?string $description = null, string $sentryTrace = '', string $baggage = ''): Transaction
    {
        $sentry = SentrySdk::getCurrentHub();
        $context = continueTrace($sentryTrace, $baggage);
        $context->setName($name);
        $context->setOp($op);
        $context->setDescription($description);

        $transaction = $sentry->startTransaction($context);

        $sentry->setSpan($transaction);

        return $transaction;
    }
}
