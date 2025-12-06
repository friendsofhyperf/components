<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Confd\Contract;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

class_alias(PsrLoggerInterface::class, LoggerInterface::class);

if (! interface_exists(LoggerInterface::class)) {
    interface LoggerInterface extends PsrLoggerInterface
    {
    }
}
