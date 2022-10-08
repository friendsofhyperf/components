<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Macros\Macros\Str;

use JsonException;

class IsJson
{
    public function __invoke()
    {
        return static function ($value) {
            if (! is_string($value)) {
                return false;
            }

            try {
                json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return false;
            }

            return true;
        };
    }
}
