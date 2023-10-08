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
 * @method self setDescription(?string $description)
 * @method self setOp(?string $op)
 * @method self setStatus(?SpanStatus $status)
 * @method self setParentSpanId(?SpanId $parentSpanId)
 * @method self setSampled(?bool $sampled)
 * @method self setSpanId(?SpanId $spanId)
 * @method self setTraceId(?TraceId $traceId)
 * @method self setTags(array $tags)
 * @method self setData(array $data)
 * @method self setStartTimestamp(?float $startTimestamp)
 * @method self setEndTimestamp(?float $endTimestamp)
 * @mixin SentrySpanContext
 */
class SpanContext
{
    protected ?Span $parentSpan;

    protected ?SentrySpanContext $spanContext = null;

    public function __construct(string $op, ?string $description = null, ?float $startTimestamp = null)
    {
        $this->parentSpan = TraceContext::getRoot() ?? SentrySdk::getCurrentHub()->getSpan();
        $this->spanContext = new SentrySpanContext();
        $this->spanContext->setOp($op);
        $this->spanContext->setDescription($description);
        $this->spanContext->setStartTimestamp($startTimestamp ?? microtime(true));
    }

    public function __call($name, $arguments)
    {
        $result = $this->spanContext?->{$name}(...$arguments);
        return str_starts_with($name, 'set') ? $this : $result;
    }

    public function finish(?float $endTimestamp = null): void
    {
        $this->spanContext->setEndTimestamp($endTimestamp ?? microtime(true));
        $this->parentSpan?->startChild($this->spanContext);
        $this->spanContext = null;
    }

    public static function create(string $op, ?string $description = null, ?float $startTimestamp = null): static
    {
        return new static($op, $description,$startTimestamp ?? microtime(true));
    }
}
