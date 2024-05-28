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

class FloatCast implements Castable
{
    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): float
    {
        if (! is_numeric($value)) {
            throw new CastException($property);
        }

        return (float) $value;
    }
}
