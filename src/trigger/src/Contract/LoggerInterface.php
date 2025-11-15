<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Contract;

class_alias(\Psr\Log\LoggerInterface::class, LoggerInterface::class);

if (! interface_exists(LoggerInterface::class)) {
    interface LoggerInterface extends \Psr\Log\LoggerInterface
    {
    }
}
