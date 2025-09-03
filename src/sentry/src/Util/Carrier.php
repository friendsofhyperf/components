<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry\Util;

use Hyperf\Collection\Arr;
use Hyperf\Contract\Arrayable;
use JsonSerializable;
use Sentry\Tracing\Span;
use Stringable;

class Carrier implements JsonSerializable, Arrayable, Stringable
{
    public function __construct(
        protected array $data = []
    ) {
    }

    public function __toString(): string
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE);
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    public static function fromJson(string $json): static
    {
        $data = (array) json_decode($json, true);

        return new static($data);
    }

    public static function fromSpan(Span $span): static
    {
        return new static([
            'sentry-trace' => $span->toTraceparent(),
            'baggage' => $span->toBaggage(),
            'traceparent' => $span->toW3CTraceparent(),
        ]);
    }

    public function with(array $data): static
    {
        $new = clone $this;
        $new->data = array_merge($this->data, $data);
        return $new;
    }

    public function withSpan(Span $span): static
    {
        return $this->with([
            'sentry-trace' => $span->toTraceparent(),
            'baggage' => $span->toBaggage(),
            'traceparent' => $span->toW3CTraceparent(),
        ]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function extract(): array
    {
        return [
            $this->data['sentry-trace'] ?? $this->data['traceparent'] ?? '',
            $this->data['baggage'] ?? '',
            $this->data['traceparent'] ?? '',
        ];
    }

    public function only(array $keys): array
    {
        return Arr::only($this->data, $keys);
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }
}
