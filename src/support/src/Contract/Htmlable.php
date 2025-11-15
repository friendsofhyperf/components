<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Support\Contract;

class_alias(\Hyperf\Contract\Htmlable::class, Htmlable::class);

if (! interface_exists(Htmlable::class)) {
    /**
     * Interface Htmlable.
     * @deprecated since v3.2, will be removed in next major version, use `\Hyperf\Contract\Htmlable` instead
     */
    interface Htmlable extends \Hyperf\Contract\Htmlable
    {
    }
}
