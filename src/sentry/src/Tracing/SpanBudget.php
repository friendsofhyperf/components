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

/**
 * Bounds the number of spans that can be created within a single transaction.
 *
 * When a long-lived coroutine keeps spawning child coroutines, the parent
 * transaction span tree would otherwise grow unboundedly and leak memory for
 * the whole lifetime of the coroutine. A limit of 0 (or negative) disables
 * the budget entirely.
 */
final class SpanBudget
{
    public const CONTEXT_KEY = 'sentry.tracing.span_budget.count';

    public function __construct(private int $limit)
    {
    }

    /**
     * Reset the counter for the current coroutine context.
     */
    public function reset(): void
    {
        Context::set(self::CONTEXT_KEY, 0);
    }

    /**
     * Try to acquire one slot of the budget.
     *
     * When the limit is not positive the budget is disabled and this method
     * always returns true without counting.
     */
    public function tryAcquire(): bool
    {
        if ($this->limit <= 0) {
            return true;
        }

        $count = Context::getOrSet(self::CONTEXT_KEY, fn () => 0);

        if ($count >= $this->limit) {
            return false;
        }

        Context::set(self::CONTEXT_KEY, $count + 1);

        return true;
    }

    /**
     * Return the number of spans already acquired in the current context.
     */
    public function count(): int
    {
        return (int) Context::get(self::CONTEXT_KEY, 0);
    }
}
