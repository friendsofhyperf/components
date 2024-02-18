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
use FriendsOfHyperf\Sentry\Util\CarrierPacker;
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
        $sentryTrace = match (true) {
            $request->hasHeader('sentry-trace') => $request->getHeaderLine('sentry-trace'),
            $request->hasHeader('traceparent') => $request->getHeaderLine('traceparent'),
            default => '',
        };
        $baggage = $request->getHeaderLine('baggage');
        $container = $this->container ?? ApplicationContext::getContainer();

        if ($container->has(RpcContext::class)) {
            $rpcContext = $container->get(RpcContext::class);
            $carrier = $rpcContext->get(Constants::TRACE_CARRIER);
            if ($carrier) {
                $packer = $container->get(CarrierPacker::class);
                [$sentryTrace, $baggage] = $packer->unpack($carrier);
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

        $transaction = $hub->startTransaction($context);
        $transaction->setStartTimestamp(microtime(true));
        $transaction->setStatus(SpanStatus::ok());

        $hub->setSpan($transaction);

        return $transaction;
    }
}
