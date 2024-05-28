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

class DoubleCast implements Castable
{
    /**
     * @throws CastException
     */
    public function cast(string $property, mixed $value): float
    {
        try {
            return (float) $value;
        } catch (Throwable $e) { // @phpstan-ignore-line
            throw new CastException($property);
        }
    }
}
