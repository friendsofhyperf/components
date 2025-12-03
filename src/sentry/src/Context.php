<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Sentry;

use FriendsOfHyperf\Sentry\Util\Carrier;
use Hyperf\Context\Context as Ctx;

class Context
{
    public const TRACE_CARRIER = 'sentry.tracing.trace_carrier';

    public const CRON_CHECKIN_ID = 'sentry.crons.checkin_id';

    public const DISABLE_COROUTINE_TRACING = 'sentry.tracing.coroutine.disabled';

    public const TRACE_SERVER_ADDRESS = 'sentry.tracing.server.address';

    public const TRACE_SERVER_PORT = 'sentry.tracing.server.port';

    public const TRACE_SPAN_DATA = 'sentry.tracing.data';

    public const TRACE_DB_SERVER_ADDRESS = 'sentry.tracing.db.server.address';

    public const TRACE_DB_SERVER_PORT = 'sentry.tracing.db.server.port';

    public const TRACE_REDIS_SERVER_ADDRESS = 'sentry.tracing.redis.server.address';

    public const TRACE_REDIS_SERVER_PORT = 'sentry.tracing.redis.server.port';

    public const TRACE_RPC_SERVER_ADDRESS = 'sentry.tracing.rpc.server.address';

    public const TRACE_RPC_SERVER_PORT = 'sentry.tracing.rpc.server.port';

    public static function disableTracing(): void
    {
        Ctx::set(self::DISABLE_COROUTINE_TRACING, true);
    }

    public static function enableTracing(): void
    {
        Ctx::set(self::DISABLE_COROUTINE_TRACING, false);
    }

    public static function isTracingEnabled(): bool
    {
        return ! Ctx::get(self::DISABLE_COROUTINE_TRACING, false);
    }

    public static function isTracingDisabled(): bool
    {
        return Ctx::get(self::DISABLE_COROUTINE_TRACING, false);
    }

    public static function setCronCheckInId(string $checkInId): void
    {
        Ctx::set(self::CRON_CHECKIN_ID, $checkInId);
    }

    public static function getCronCheckInId(): ?string
    {
        return Ctx::get(self::CRON_CHECKIN_ID);
    }

    public static function setCarrier(Carrier $carrier): void
    {
        Ctx::set(self::TRACE_CARRIER, $carrier);
    }

    public static function getCarrier(?int $coroutineId = null): ?Carrier
    {
        return Ctx::get(self::TRACE_CARRIER, coroutineId: $coroutineId);
    }

    public static function setRedisServerAddress(string $address): void
    {
        Ctx::set(self::TRACE_REDIS_SERVER_ADDRESS, $address);
    }

    public static function getRedisServerAddress(): ?string
    {
        return Ctx::get(self::TRACE_REDIS_SERVER_ADDRESS);
    }

    public static function setRedisServerPort(int $port): void
    {
        Ctx::set(self::TRACE_REDIS_SERVER_PORT, $port);
    }

    public static function getRedisServerPort(): ?int
    {
        return Ctx::get(self::TRACE_REDIS_SERVER_PORT);
    }

    public static function setServerAddress(string $address): void
    {
        Ctx::set(self::TRACE_SERVER_ADDRESS, $address);
    }

    public static function getServerAddress(): ?string
    {
        return Ctx::get(self::TRACE_SERVER_ADDRESS);
    }

    public static function setServerPort(int $port): void
    {
        Ctx::set(self::TRACE_SERVER_PORT, $port);
    }

    public static function getServerPort(): ?int
    {
        return Ctx::get(self::TRACE_SERVER_PORT);
    }

    public static function setRpcServerAddress(string $address): void
    {
        Ctx::set(self::TRACE_RPC_SERVER_ADDRESS, $address);
    }

    public static function getRpcServerAddress(): ?string
    {
        return Ctx::get(self::TRACE_RPC_SERVER_ADDRESS);
    }

    public static function setRpcServerPort(int $port): void
    {
        Ctx::set(self::TRACE_RPC_SERVER_PORT, $port);
    }

    public static function getRpcServerPort(): ?int
    {
        return Ctx::get(self::TRACE_RPC_SERVER_PORT);
    }

    public static function setDbServerAddress(string $address): void
    {
        Ctx::set(self::TRACE_DB_SERVER_ADDRESS, $address);
    }

    public static function getDbServerAddress(): ?string
    {
        return Ctx::get(self::TRACE_DB_SERVER_ADDRESS);
    }

    public static function setDbServerPort(int $port): void
    {
        Ctx::set(self::TRACE_DB_SERVER_PORT, $port);
    }

    public static function getDbServerPort(): ?int
    {
        return Ctx::get(self::TRACE_DB_SERVER_PORT);
    }

    public static function setSpanData(array $data): void
    {
        Ctx::set(self::TRACE_SPAN_DATA, $data);
    }

    public static function getSpanData(): ?array
    {
        return Ctx::get(self::TRACE_SPAN_DATA);
    }
}
