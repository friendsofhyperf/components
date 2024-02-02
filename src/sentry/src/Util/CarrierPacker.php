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

use Sentry\Tracing\Span;
use Throwable;

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
                $carrier['sentry-trace'] ?? $carrier['traceparent'] ?? '',
                $carrier['baggage'] ?? '',
                $carrier['traceparent'] ?? '',
            ];
        } catch (Throwable) {
            return ['', '', ''];
        }
    }

    public function pack(Span $span): string
    {
        return json_encode([
            'sentry-trace' => $span->toTraceparent(),
            'baggage' => $span->toBaggage(),
            'traceparent' => $span->toW3CTraceparent(),
        ]);
    }
}
