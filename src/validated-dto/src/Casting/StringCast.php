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
use Throwable;

class StringCast implements Castable
{
    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): string
    {
        try {
            return (string) $value;
        } catch (Throwable) {
            throw new CastException($property);
        }
    }
}
