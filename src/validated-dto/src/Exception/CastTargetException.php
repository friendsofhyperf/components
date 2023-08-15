<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ValidatedDTO\Exception;

use Exception;

class CastTargetException extends Exception
{
    public function __construct(string $property)
    {
        parent::__construct("The property: {$property} has an invalid cast configuration", 422);
    }
}
