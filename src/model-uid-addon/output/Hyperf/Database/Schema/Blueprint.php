<?php

declare(strict_types=1);
/**
 * This file is part of model-uid-addon.
 *
 * @link     https://github.com/friendsofhyperf/model-uid-addon
 * @document https://github.com/friendsofhyperf/model-uid-addon/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace Hyperf\Database\Schema;

class Blueprint
{
    /**
     * Create a new UUID column on the table.
     *
     * @param string $column
     * @return ColumnDefinition
     */
    public function uuid($column = 'uuid')
    {
    }

    /**
     * Create a new UUID column on the table with a foreign key constraint.
     *
     * @param string $column
     * @return ForeignIdColumnDefinition
     */
    public function foreignUuid($column)
    {
    }

    /**
     * Create a new ULID column on the table.
     *
     * @param string $column
     * @param null|int $length
     * @return ColumnDefinition
     */
    public function ulid($column = 'uuid', $length = 26)
    {
    }

    /**
     * Create a new ULID column on the table with a foreign key constraint.
     *
     * @param string $column
     * @param null|int $length
     * @return ForeignIdColumnDefinition
     */
    public function foreignUlid($column, $length = 26)
    {
    }
}
