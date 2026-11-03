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

use DateTimeInterface;
use Throwable;
use UnitEnum;

/**
 * Bounds what spans and breadcrumbs keep in memory until the event is sent.
 * Objects are never kept: they may hold far more than they show, and casting one (e.g. a stream) could read it all.
 */
class PayloadLimiter
{
    public const MAX_BYTES = 8192; // 8 KB

    public const MAX_DEPTH = 5;

    private const TRUNCATED = '… [truncated]';

    private const SCALAR_BYTES = 8;

    private const GUZZLE_OPTIONS = [
        'allow_redirects',
        'connect_timeout',
        'http_errors',
        'read_timeout',
        'stream',
        'synchronous',
        'timeout',
        'verify',
        'version',
    ];

    public static function limit(mixed $value, int $maxBytes = self::MAX_BYTES): mixed
    {
        return self::walk($value, $maxBytes, self::MAX_DEPTH);
    }

    public static function limitString(string $value, int $maxBytes = self::MAX_BYTES): string
    {
        return strlen($value) > $maxBytes ? substr($value, 0, $maxBytes) . self::TRUNCATED : $value;
    }

    /**
     * Keeps only the scalar transfer settings: body, json, multipart, handler and credentials stay out.
     *
     * @param array<array-key, mixed> $options
     * @return array<string, bool|float|int|string>
     */
    public static function guzzleOptions(array $options): array
    {
        return array_map(
            static fn ($option) => is_string($option) ? self::limitString($option) : $option,
            array_filter(array_intersect_key($options, array_flip(self::GUZZLE_OPTIONS)), is_scalar(...))
        );
    }

    private static function walk(mixed $value, int &$budget, int $depth): mixed
    {
        if (is_string($value)) {
            $limited = self::limitString($value, max($budget, 0));
            $budget -= strlen($value);

            return $limited;
        }

        if (is_array($value)) {
            return self::walkArray($value, $budget, $depth);
        }

        if (is_object($value)) {
            return self::walk(self::describeObject($value), $budget, $depth);
        }

        $budget -= self::SCALAR_BYTES;

        return is_resource($value) ? '[resource]' : $value;
    }

    /**
     * @param array<array-key, mixed> $value
     * @return array<array-key, mixed>|string
     */
    private static function walkArray(array $value, int &$budget, int $depth): array|string
    {
        if ($depth <= 0) {
            return sprintf('[array(%d)]', count($value));
        }

        $limited = [];
        $remaining = count($value);

        foreach ($value as $key => $item) {
            $keyBytes = is_string($key) ? strlen($key) : self::SCALAR_BYTES;

            // A key is kept whole, so one that does not fit must stay out: it could be larger than any limit.
            if ($budget <= 0 || $keyBytes > $budget) {
                $limited[self::TRUNCATED] = $remaining;
                break;
            }

            $budget -= $keyBytes;
            $limited[$key] = self::walk($item, $budget, $depth - 1);
            --$remaining;
        }

        return $limited;
    }

    private static function describeObject(object $value): string
    {
        return match (true) {
            $value instanceof Throwable => sprintf('%s: %s', $value::class, $value->getMessage()),
            $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
            $value instanceof UnitEnum => sprintf('%s::%s', $value::class, $value->name),
            default => sprintf('[object %s]', $value::class),
        };
    }
}
