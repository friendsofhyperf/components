<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\ForeignIdColumnDefinition;

if (! Blueprint::hasMacro('ulid')) {
    Blueprint::macro('ulid', fn ($column = 'ulid', $length = 26) => $this->char($column, $length));
}

if (! Blueprint::hasMacro('foreignUlid')) {
    Blueprint::macro('foreignUlid', fn ($column, $length = 26) => $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
        'type' => 'char',
        'name' => $column,
        'length' => $length,
    ])));
}

if (! Blueprint::hasMacro('uuid')) {
    Blueprint::macro('uuid', fn ($column = 'uuid') => $this->addColumn('uuid', $column));
}

if (! Blueprint::hasMacro('foreignUuid')) {
    Blueprint::macro('foreignUuid', fn ($column) => $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
        'type' => 'uuid',
        'name' => $column,
    ])));
}
