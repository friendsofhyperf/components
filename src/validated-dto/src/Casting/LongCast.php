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

class LongCast implements Castable
{
    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): int
    {
        try {
            return (int) $value;
        } catch (Throwable $e) {
            throw new CastException($property);
        }
    }
}
