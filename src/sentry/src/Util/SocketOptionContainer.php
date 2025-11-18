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

use Hyperf\Engine\Contract\Socket\SocketOptionInterface;
use Hyperf\Engine\Contract\SocketInterface;
use WeakMap;

class SocketOptionContainer
{
    /**
     * @var WeakMap<SocketInterface,SocketOptionInterface>
     */
    private static ?WeakMap $map = null;

    public static function set(SocketInterface $socket, SocketOptionInterface $option): void
    {
        self::$map ??= new WeakMap();
        self::$map[$socket] = $option;
    }

    public static function get(?SocketInterface $socket): ?SocketOptionInterface
    {
        if ($socket === null) {
            return null;
        }

        self::$map ??= new WeakMap();

        return self::$map[$socket] ?? null;
    }
}
