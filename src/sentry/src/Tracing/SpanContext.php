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
use Sentry\Tracing\SpanId;
use Sentry\Tracing\SpanStatus;
use Sentry\Tracing\TraceId;

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
 * @deprecated v3.0, will be removed in v3.1
 */
class SpanContext
{
    protected ?Span $parent;

    protected ?SentrySpanContext $context = null;

    public function __construct(string $op, ?string $description = null, ?float $startTimestamp = null)
    {
        $this->parent = TraceContext::getSpan() ?? SentrySdk::getCurrentHub()->getSpan();
        $this->context = new SentrySpanContext();
        $this->context->setOp($op);
        $this->context->setDescription($description);
        $this->context->setStartTimestamp($startTimestamp ?? microtime(true));
    }

    public function __call($name, $arguments)
    {
        $result = $this->context?->{$name}(...$arguments);
        return str_starts_with($name, 'set') ? $this : $result;
    }

    public function finish(?float $endTimestamp = null): void
    {
        $this->context->setEndTimestamp($endTimestamp ?? microtime(true));
        $this->parent?->startChild($this->context);
        $this->context = null;
    }

    public static function create(string $op, ?string $description = null, ?float $startTimestamp = null): static
    {
        return new static($op, $description,$startTimestamp ?? microtime(true));
    }
}
