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

use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use JsonException;
use JsonSerializable;
use Sentry\Tracing\Span;
use Stringable;

class Carrier implements JsonSerializable, Arrayable, Stringable, Jsonable
{
    public function __construct(
        protected array $data = []
    ) {
    }

    public function __toString(): string
    {
        return $this->toJson();
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    public static function fromJson(string $json): static
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($data)) {
                $data = [];
            }
        } catch (JsonException $e) {
            $data = [];
        }

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

    public function getSentryTrace(): string
    {
        return $this->data['sentry-trace'] ?? '';
    }

    public function getBaggage(): string
    {
        return $this->data['baggage'] ?? '';
    }

    public function getTraceparent(): string
    {
        return $this->data['traceparent'] ?? '';
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        try {
            return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return '{}';
        }
    }
}
