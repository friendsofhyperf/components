<?php

declare(strict_types=1);
/**
 * This file is part of model-uid-addon.
 *
 * @link     https://github.com/friendsofhyperf/model-uid-addon
 * @document https://github.com/friendsofhyperf/model-uid-addon/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\ForeignIdColumnDefinition;

if (! Blueprint::hasMacro('ulid')) {
    Blueprint::macro('ulid', function ($column = 'ulid', $length = 26) {
        return $this->char($column, $length);
    });
}

if (! Blueprint::hasMacro('foreignUlid')) {
    Blueprint::macro('foreignUlid', function ($column, $length = 26) {
        return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
            'type' => 'char',
            'name' => $column,
            'length' => $length,
        ]));
    });
}

if (! Blueprint::hasMacro('uuid')) {
    Blueprint::macro('uuid', function ($column = 'uuid') {
        return $this->addColumn('uuid', $column);
    });
}

if (! Blueprint::hasMacro('foreignUuid')) {
    Blueprint::macro('foreignUuid', function ($column, $length = 26) {
        return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
            'type' => 'uuid',
            'name' => $column,
        ]));
    });
}
