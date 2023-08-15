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
use FriendsOfHyperf\ValidatedDTO\SimpleDTO;

class SimpleUserDTO extends SimpleDTO
{
    public SimpleNameDTO $name;

    public string $email;

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'name' => new DTOCast(SimpleNameDTO::class),
        ];
    }

    protected function mapBeforeExport(): array
    {
        return [
            'name.first_name' => 'first_name',
            'name.last_name' => 'last_name',
        ];
    }
}
