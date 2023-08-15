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

class MissingCastTypeException extends Exception
{
    public function __construct(string $property)
    {
        parent::__construct("Missing cast type configuration for property: {$property}", 422);
    }
}
