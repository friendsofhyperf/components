<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Tests\ValidatedDTO\Datasets;

use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use FriendsOfHyperf\ValidatedDTO\SimpleDTO;

class SimpleOuterDTO extends SimpleDTO
{
    public string $name;

    public SimpleInnerDTO $inner;

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'name' => new StringCast(),
            'number' => new IntegerCast(),
            'inner' => new DTOCast(SimpleInnerDTO::class),
        ];
    }
}
