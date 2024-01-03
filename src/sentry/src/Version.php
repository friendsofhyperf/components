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

use Hyperf\Support\Composer;

final class Version
{
    public const SDK_IDENTIFIER = 'sentry.php.hyperf';

    public const SDK_VERSION = '3.0.0';

    public static function getSdkIdentifier(): string
    {
        return self::SDK_IDENTIFIER;
    }

    public static function getSdkVersion(): string
    {
        return Composer::getVersions()['friendsofhyperf/sentry'] ?? self::SDK_VERSION;
    }
}
