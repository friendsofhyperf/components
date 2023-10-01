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
use Hyperf\Coroutine\WaitGroup;
use Sentry\Tracing\Span;
use Sentry\Tracing\Transaction;

class TraceContext
{
    public const ROOT = 'sentry.tracing.root';

    public const TRANSACTION = 'sentry.tracing.transaction';

    public const RPC_CARRIER = 'sentry.tracing.rpc.carrier';

    public const WAIT_GROUP = 'sentry.tracing.wait_group';

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

    public static function setRoot(Span $context): Span
    {
        return Context::set(self::ROOT, $context);
    }

    public static function getRoot(): ?Span
    {
        return Context::get(self::ROOT);
    }

    public static function clearRoot(): void
    {
        Context::set(self::ROOT, null);
    }

    public static function getWaitGroup(): WaitGroup
    {
        return Context::getOrSet(self::WAIT_GROUP, fn () => new WaitGroup());
    }
}
