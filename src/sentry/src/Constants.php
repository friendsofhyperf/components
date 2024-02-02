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

    public const CRON_CHECKIN_ID = 'sentry.crons.checkin_id';

    public const DISABLE_COROUTINE_TRACING = 'sentry.tracing.disable_coroutine_tracing';
}
