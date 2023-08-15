<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Casting;

class ArrayCast implements Castable
{
    public function cast(string $property, mixed $value): array
    {
        if (is_string($value)) {
            $jsonDecoded = json_decode($value, true);

            return is_array($jsonDecoded) ? $jsonDecoded : [$value];
        }

        return is_array($value) ? $value : [$value];
    }
}
