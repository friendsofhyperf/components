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

class InvalidJsonException extends Exception
{
    public function __construct()
    {
        parent::__construct('The JSON string provided is not valid', 422);
    }
}
