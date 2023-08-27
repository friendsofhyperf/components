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

use FriendsOfHyperf\ValidatedDTO\Casting\IntegerCast;
use FriendsOfHyperf\ValidatedDTO\Casting\StringCast;
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;

class NullableDTO extends ValidatedDTO
{
    public string $name;

    public ?int $age;

    public ?string $address;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'age' => ['optional', 'integer'],
            'address' => ['nullable', 'string'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'name' => new StringCast(),
            'age' => new IntegerCast(),
            'address' => new StringCast(),
        ];
    }
}
