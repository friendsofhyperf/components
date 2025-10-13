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

use FriendsOfHyperf\ValidatedDTO\Attributes\Cast;
use FriendsOfHyperf\ValidatedDTO\Casting\DTOCast;
use FriendsOfHyperf\ValidatedDTO\Exception\CastException;
use FriendsOfHyperf\ValidatedDTO\SimpleDTO;

class CallableCastingDTOInstance extends SimpleDTO
{
    #[Cast(DTOCast::class, SimpleNameDTO::class)]
    public SimpleNameDTO $name;

    public ?int $age = null;

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'age' => function (string $property, mixed $value) {
                if (! is_numeric($value)) {
                    throw new CastException($property);
                }

                return (int) $value;
            },
        ];
    }
}
