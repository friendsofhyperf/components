<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger;

use RuntimeException;

class Util
{
    /**
     * @throws RuntimeException
     */
    public static function getInternalIp(): string
    {
        $ips = swoole_get_local_ip();

        if (is_array($ips) && ! empty($ips)) {
            return current($ips);
        }

        /** @var mixed|string $ip */
        $ip = gethostbyname(gethostname());
        if (is_string($ip)) {
            return $ip;
        }

        throw new RuntimeException('Can not get the internal IP.');
    }
}
