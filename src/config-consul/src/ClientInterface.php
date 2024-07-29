<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ConfigConsul;

class_alias(\Hyperf\ConfigCenter\Contract\ClientInterface::class, ClientInterface::class);

if (false) { // @phpstan-ignore-line
    // @codeCoverageIgnoreStart
    interface ClientInterface extends \Hyperf\ConfigCenter\Contract\ClientInterface
    {
    }
    // @codeCoverageIgnoreEnd
}
