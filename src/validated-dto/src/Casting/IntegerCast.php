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

use FriendsOfHyperf\ValidatedDTO\Exception\CastException;

class IntegerCast implements Castable
{
    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): int
    {
        if (! is_numeric($value)) {
            throw new CastException($property);
        }

        return (int) $value;
    }
}
