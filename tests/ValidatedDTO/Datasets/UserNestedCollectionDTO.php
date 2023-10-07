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
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;
use Hyperf\Collection\Collection;

class UserNestedCollectionDTO extends ValidatedDTO
{
    public Collection $names;

    public string $email;

    protected function rules(): array
    {
        return [
            'names' => ['required', 'array'],
            'email' => ['required', 'email'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'names' => new CollectionCast(new DTOCast(NameDTO::class)),
        ];
    }
}
