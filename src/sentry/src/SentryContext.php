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

class SentryContext
{
    public const SERVER_NAME = 'sentry.context.server_name';

    public static function setServerName(string $serverName): void
    {
        Context::set(self::SERVER_NAME, $serverName);
    }

    public static function getServerName(): ?string
    {
        return Context::has(self::SERVER_NAME) ? (string) Context::get(self::SERVER_NAME) : null;
    }
}
