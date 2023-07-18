<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Trigger\Event;

use MySQLReplication\BinLog\BinLogCurrent;

class OnReplicationStop
{
    public function __construct(public string $connection, public ?BinLogCurrent $binLogCurrent = null)
    {
    }
}
