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

/**
 * @deprecated since v3.1, use \MySQLReplication\Definitions\ConstEventsNames instead, will be removed in v3.2
 */
enum ConstEventsNames: string
{
    case XID = 'xid';
    case DELETE = 'delete';
    case QUERY = 'query';
    case ROTATE = 'rotate';
    case GTID = 'gtid';
    case MARIADB_GTID = 'mariadb gtid';
    case UPDATE = 'update';
    case HEARTBEAT = 'heartbeat';
    case TABLE_MAP = 'tableMap';
    case WRITE = 'write';
    case FORMAT_DESCRIPTION = 'format description';
    case ROWS_QUERY = 'rows_query';
}
