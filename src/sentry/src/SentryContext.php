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
use Hyperf\Context\Context;
use Sentry\Tracing\SpanContext;

class SentryContext
{
    public const CTX_CRON_CHECKIN_ID = 'sentry.ctx.crons.checkin_id';

    public const CTX_DISABLE_COROUTINE_TRACING = 'sentry.ctx.coroutine.disabled';

    public const CTX_CARRIER = 'sentry.ctx.carrier';

    public const CTX_ELASTICSEARCH_SPAN_DATA = 'sentry.ctx.elasticsearch.span.data';

    public const CTX_DB_SERVER_ADDRESS = 'sentry.ctx.db.server.address';

    public const CTX_DB_SERVER_PORT = 'sentry.ctx.db.server.port';

    public const CTX_REDIS_SERVER_ADDRESS = 'sentry.ctx.redis.server.address';

    public const CTX_REDIS_SERVER_PORT = 'sentry.ctx.redis.server.port';

    public const CTX_RPC_SERVER_ADDRESS = 'sentry.ctx.rpc.server.address';

    public const CTX_RPC_SERVER_PORT = 'sentry.ctx.rpc.server.port';

    public const CTX_RPC_SPAN_CONTEXT = 'sentry.ctx.rpc.span.context';

    public static function disableTracing(): void
    {
        Context::set(self::CTX_DISABLE_COROUTINE_TRACING, true);
    }

    public static function enableTracing(): void
    {
        Context::set(self::CTX_DISABLE_COROUTINE_TRACING, false);
    }

    public static function isTracingDisabled(): bool
    {
        return (bool) Context::get(self::CTX_DISABLE_COROUTINE_TRACING, false);
    }

    public static function setCronCheckInId(string $checkInId): void
    {
        Context::set(self::CTX_CRON_CHECKIN_ID, $checkInId);
    }

    public static function getCronCheckInId(): ?string
    {
        return Context::get(self::CTX_CRON_CHECKIN_ID);
    }

    public static function setCarrier(Carrier $carrier): void
    {
        Context::set(self::CTX_CARRIER, $carrier);
    }

    public static function getCarrier(?int $coroutineId = null): ?Carrier
    {
        return Context::get(self::CTX_CARRIER, coroutineId: $coroutineId);
    }

    public static function setRedisServerAddress(string $address): void
    {
        Context::set(self::CTX_REDIS_SERVER_ADDRESS, $address);
    }

    public static function getRedisServerAddress(): ?string
    {
        return Context::get(self::CTX_REDIS_SERVER_ADDRESS);
    }

    public static function setRedisServerPort(int $port): void
    {
        Context::set(self::CTX_REDIS_SERVER_PORT, $port);
    }

    public static function getRedisServerPort(): ?int
    {
        return Context::get(self::CTX_REDIS_SERVER_PORT);
    }

    public static function setRpcServerAddress(string $address): void
    {
        Context::set(self::CTX_RPC_SERVER_ADDRESS, $address);
    }

    public static function getRpcServerAddress(): ?string
    {
        return Context::get(self::CTX_RPC_SERVER_ADDRESS);
    }

    public static function setRpcServerPort(int $port): void
    {
        Context::set(self::CTX_RPC_SERVER_PORT, $port);
    }

    public static function getRpcServerPort(): ?int
    {
        return Context::get(self::CTX_RPC_SERVER_PORT);
    }

    public static function setDbServerAddress(string $address): void
    {
        Context::set(self::CTX_DB_SERVER_ADDRESS, $address);
    }

    public static function getDbServerAddress(): ?string
    {
        return Context::get(self::CTX_DB_SERVER_ADDRESS);
    }

    public static function setDbServerPort(int $port): void
    {
        Context::set(self::CTX_DB_SERVER_PORT, $port);
    }

    public static function getDbServerPort(): ?int
    {
        return Context::get(self::CTX_DB_SERVER_PORT);
    }

    public static function setElasticsearchSpanData(array $data): void
    {
        Context::set(self::CTX_ELASTICSEARCH_SPAN_DATA, $data);
    }

    public static function getElasticsearchSpanData(): ?array
    {
        return Context::get(self::CTX_ELASTICSEARCH_SPAN_DATA);
    }

    public static function setRpcSpanContext(SpanContext $spanContext): void
    {
        Context::set(self::CTX_RPC_SPAN_CONTEXT, $spanContext);
    }

    public static function getRpcSpanContext(): ?SpanContext
    {
        return Context::get(self::CTX_RPC_SPAN_CONTEXT);
    }

    public static function destroyRpcSpanContext(): void
    {
        Context::destroy(self::CTX_RPC_SPAN_CONTEXT);
    }
}
