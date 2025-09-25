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

use FriendsOfHyperf\Sentry\Tracing\Tracer;
use Hyperf\Context\ApplicationContext;
use Sentry\State\Scope;
use Sentry\Tracing\SpanContext;
use Sentry\Tracing\Transaction;
use Sentry\Tracing\TransactionContext;

/**
 * Starts a new Transaction and returns it. This is the entry point to manual tracing instrumentation.
 */
function startTransaction(TransactionContext $transactionContext, array $customSamplingContext = []): Transaction
{
    return ApplicationContext::getContainer()
        ->get(Tracer::class)
        ->startTransaction($transactionContext, $customSamplingContext);
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
    return ApplicationContext::getContainer()
        ->get(Tracer::class)
        ->trace($trace, $context);
}
