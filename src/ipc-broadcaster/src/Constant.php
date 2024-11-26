<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\IpcBroadcaster;

final class Constant
{
    public static bool $isCoroutineServer = false;

    public static function setCoroutineServer(bool $isCoroutineServer): void
    {
        self::$isCoroutineServer = $isCoroutineServer;
    }

    public static function isCoroutineServer(): bool
    {
        return self::$isCoroutineServer;
    }
}
