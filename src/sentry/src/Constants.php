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

class Constants
{
    public const TRACE_CARRIER = 'sentry.tracing.trace_carrier';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_DB_SERVER_ADDRESS = 'sentry.tracing.db.server.address';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_DB_SERVER_PORT = 'sentry.tracing.db.server.port';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_REDIS_SERVER_ADDRESS = 'sentry.tracing.redis.server.address';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_REDIS_SERVER_PORT = 'sentry.tracing.redis.server.port';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_RPC_SERVER_ADDRESS = 'sentry.tracing.rpc.server.address';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_RPC_SERVER_PORT = 'sentry.tracing.rpc.server.port';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const TRACE_ELASTICSEARCH_REQUEST_DATA = 'sentry.tracing.elasticsearch.request.data';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const CRON_CHECKIN_ID = 'sentry.crons.checkin_id';

    /**
     * @deprecated since v3.1, will be removed in v3.2.
     */
    public const DISABLE_COROUTINE_TRACING = 'sentry.tracing.coroutine.disabled';

    public const SENTRY_TRACE = 'sentry-trace';

    public const BAGGAGE = 'baggage';

    public const TRACEPARENT = 'traceparent';

    public static bool $runningInCommand = false;
}
