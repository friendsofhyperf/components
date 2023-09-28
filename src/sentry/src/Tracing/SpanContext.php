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

use Sentry\SentrySdk;
use Sentry\Tracing\Span;
use Sentry\Tracing\SpanContext as SentrySpanContext;

/**
 * @mixin SentrySpanContext
 */
class SpanContext
{
    protected ?Span $parentSpan;

    protected SentrySpanContext $spanContext;

    public function __construct()
    {
        $this->parentSpan = SentrySdk::getCurrentHub()->getSpan();
        $this->spanContext = new SentrySpanContext();
    }

    public function __call($name, $arguments)
    {
        return $this->spanContext->{$name}(...$arguments);
    }

    public function start()
    {
        // if (! $this->spanContext->getStartTimestamp()) {
        $this->spanContext->setEndTimestamp(microtime(true));
        // }

        $this->parentSpan?->startChild($this->spanContext);
    }

    public static function create(string $op, ?string $description = null, ?float $startTime = null): static
    {
        $startTime ??= microtime(true);
        $context = new static();
        $context->spanContext->setOp($op);
        $description && $context->spanContext->setDescription($description);
        $context->spanContext->setStartTimestamp($startTime);
        return $context;
    }
}
