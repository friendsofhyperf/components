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

use FriendsOfHyperf\ValidatedDTO\Casting\CollectionCast;
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use FriendsOfHyperf\ValidatedDTO\Concerns\Wireable;
use FriendsOfHyperf\ValidatedDTO\SimpleDTO;
use Hyperf\Collection\Collection;

class WireableDTO extends SimpleDTO
{
    use Wireable;

    public ?string $name;

    public ?int $age;

    public ?SimpleNameDTO $simple_name_dto;

    public ?Collection $simple_names_collection;

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'name' => new StringCast(),
            'age' => new IntegerCast(),
            'simple_name_dto' => new DTOCast(SimpleNameDTO::class),
            'simple_names_collection' => new CollectionCast(new DTOCast(SimpleNameDTO::class)),
        ];
    }
}
