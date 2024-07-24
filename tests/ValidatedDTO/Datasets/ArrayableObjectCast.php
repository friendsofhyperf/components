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

use FriendsOfHyperf\ValidatedDTO\Casting\Castable;
use Hyperf\Contract\Arrayable;

class ArrayableObjectCast implements Castable
{
    public function cast(string $property, mixed $value): Arrayable
    {
        return new ArrayableObject();
    }
}
