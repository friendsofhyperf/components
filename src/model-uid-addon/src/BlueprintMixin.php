<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.0/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelUidAddon;

use Hyperf\Database\Schema\ForeignIdColumnDefinition;

/**
 * @mixin \Hyperf\Database\Schema\Blueprint
 */
class BlueprintMixin
{
    public function ulid()
    {
        return fn ($column = 'ulid', $length = 26) => $this->char($column, $length);
    }

    public function foreignUlid()
    {
        /* @phpstan-ignore-next-line */
        return fn ($column, $length = 26) => $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
            'type' => 'char',
            'name' => $column,
            'length' => $length,
        ]));
    }

    public function uuid()
    {
        return fn ($column = 'uuid') => $this->addColumn('uuid', $column);
    }

    public function foreignUuid()
    {
        /* @phpstan-ignore-next-line */
        return fn ($column) => $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
            'type' => 'uuid',
            'name' => $column,
        ]));
    }
}
