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

use Hyperf\Context\Context;
use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;

class TraceContext
{
    public const RPC_CARRIER = 'sentry.tracing.rpc_carrier';

    public const SPAN = 'sentry.tracing.span';

    public const TRANSACTION = 'sentry.tracing.transaction';

    public static function setTransaction(Transaction $transaction): Transaction
    {
        return Context::set(self::TRANSACTION, $transaction);
    }

    public static function getTransaction(): ?Transaction
    {
        return Context::get(self::TRANSACTION);
    }

    public static function clearTransaction(): void
    {
        Context::set(self::TRANSACTION, null);
    }

    public static function setSpan(Span $context): Span
    {
        return Context::set(self::SPAN, $context);
    }

    public static function getSpan(): ?Span
    {
        return Context::get(self::SPAN);
    }

    public static function clearSpan(): void
    {
        Context::set(self::SPAN, null);
    }
}
