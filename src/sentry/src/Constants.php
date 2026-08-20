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

use Hyperf\Context\Context;

class Constants
{
    public const TRACE_CARRIER = 'sentry.tracing.trace_carrier';

    public const SENTRY_TRACE = 'sentry-trace';

    public const BAGGAGE = 'baggage';

    public const TRACEPARENT = 'traceparent';

    public const CTX_RUNNING_IN_COMMAND = 'sentry.constants.running_in_command';

    public static function runningInCommand(): bool
    {
        return (bool) Context::get(self::CTX_RUNNING_IN_COMMAND, false);
    }

    public static function setRunningInCommand(bool $running = true): void
    {
        Context::set(self::CTX_RUNNING_IN_COMMAND, $running);
    }
}
