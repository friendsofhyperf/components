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
use FriendsOfHyperf\Sentry\Util\Carrier;
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
    protected function startSpan(
        ?string $op = null,
        ?string $description = null,
        ?string $origin = null,
        bool $asParent = false
    ): ?Span {
        if (! $parent = SentrySdk::getCurrentHub()->getSpan()) {
            return null;
        }

        return tap(
            $parent->startChild(new SpanContext())
                ->setOp($op)
                ->setDescription($description)
                ->setOrigin($origin)
                ->setStatus(SpanStatus::ok())
                ->setStartTimestamp(microtime(true)),
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
            /** @var string|null $payload */
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

        $context = continueTrace($sentryTrace, $baggage);
        if (isset($options['name']) && is_string($options['name'])) {
            $context->setName($options['name']);
        }
        if (isset($options['op']) && is_string($options['op'])) {
            $context->setOp($options['op']);
        }
        if (isset($options['description']) && is_string($options['description'])) {
            $context->setDescription($options['description']);
        }
        if (isset($options['origin']) && is_string($options['origin'])) {
            $context->setOrigin($options['origin']);
        }
        if (isset($options['source']) && $options['source'] instanceof TransactionSource) {
            $context->setSource($options['source']);
        } else {
            $context->setSource(TransactionSource::custom());
        }

        $transaction = $hub->startTransaction($context)
            ->setStartTimestamp(microtime(true))
            ->setStatus(SpanStatus::ok());

        $hub->setSpan($transaction);

        return $transaction;
    }
}
