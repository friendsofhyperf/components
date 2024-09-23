<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Trigger\Trigger;

use Hyperf\Context\Context as HyperfContext;
use MySQLReplication\Event\DTO\RowsDTO;

class Context
{
    public const DATABASE = 'trigger.context.database';

    public const TABLE = 'trigger.context.table';

    public const EVENT_TYPE = 'trigger.context.event';

    public const EVENT = 'trigger.context.event';

    public static function setDatabase(string $database): void
    {
        HyperfContext::set(static::DATABASE, $database);
    }

    public static function getDatabase(): ?string
    {
        return HyperfContext::get(static::DATABASE);
    }

    public static function setTable(string $table): void
    {
        HyperfContext::set(static::TABLE, $table);
    }

    public static function getTable(): ?string
    {
        return HyperfContext::get(static::TABLE);
    }

    public static function setEventType(string $event): void
    {
        HyperfContext::set(static::EVENT_TYPE, $event);
    }

    public static function getEventType(): ?string
    {
        return HyperfContext::get(static::EVENT_TYPE);
    }

    public static function setEvent(RowsDTO $event): void
    {
        HyperfContext::set(static::EVENT, $event);
    }

    public static function getEvent(): ?RowsDTO
    {
        return HyperfContext::get(static::EVENT);
    }
}
