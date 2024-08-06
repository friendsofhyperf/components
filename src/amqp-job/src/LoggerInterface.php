<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\AmqpJob;

class_alias(Contract\LoggerInterface::class, LoggerInterface::class);

// @phpstan-ignore-next-line
if (false) {
    /**
     * @deprecated since v3.1, use FriendsOfHyperf\AmqpJob\Contract\LoggerInterface instead, will removed at v3.2.
     */
    interface LoggerInterface extends Contract\LoggerInterface
    {
    }
}
