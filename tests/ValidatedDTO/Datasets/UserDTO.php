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
use FriendsOfHyperf\ValidatedDTO\ValidatedDTO;

class UserDTO extends ValidatedDTO
{
    public NameDTO $name;

    public string $email;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'array'],
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
            'name' => new DTOCast(NameDTO::class),
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
