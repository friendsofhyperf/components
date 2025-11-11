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

use FriendsOfHyperf\Sentry\Constants;
use Sentry\Tracing\Span;
use Throwable;

/**
 * @deprecated since v3.1, use FriendsOfHyperf\Sentry\Util\Carrier instead, will be removed in v3.2
 */
class CarrierPacker
{
    /**
     * @return string[]
     */
    public function unpack(string $data): array
    {
        try {
            $carrier = json_decode($data, true, flags: JSON_THROW_ON_ERROR);

            return [
                $carrier[Constants::SENTRY_TRACE] ?? $carrier[Constants::TRACEPARENT] ?? '',
                $carrier[Constants::BAGGAGE] ?? '',
                $carrier[Constants::TRACEPARENT] ?? '',
            ];
        } catch (Throwable) {
            return ['', '', ''];
        }
    }

    public function pack(Span $span, array $extra = []): string
    {
        return json_encode([
            Constants::SENTRY_TRACE => $span->toTraceparent(),
            Constants::BAGGAGE => $span->toBaggage(),
            ...$extra,
        ]);
    }
}
