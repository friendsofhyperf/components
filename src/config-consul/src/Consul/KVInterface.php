<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ConfigConsul\Consul;

class_alias(\Hyperf\Consul\KVInterface::class, KVInterface::class);

if (! interface_exists(KVInterface::class)) {
    // @codeCoverageIgnoreStart
    interface KVInterface extends \Hyperf\Consul\KVInterface
    {
    }
    // @codeCoverageIgnoreEnd
}
