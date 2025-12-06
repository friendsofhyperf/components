<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\MonologHook\Aspect;

class_alias(\Hyperf\Logger\Aspect\UdpSocketAspect::class, UdpSocketAspect::class);

if (! class_exists(UdpSocketAspect::class)) {
    /**
     * @deprecated use `\Hyperf\Logger\Aspect\UdpSocketAspect` instead
     */
    class UdpSocketAspect extends \Hyperf\Logger\Aspect\UdpSocketAspect
    {
    }
}
