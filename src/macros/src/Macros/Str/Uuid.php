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

use FriendsOfHyperf\Macros\Foundation\UuidContainer;
use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    public function __invoke()
    {
        return static function () {
            return UuidContainer::$uuidFactory
                        ? call_user_func(UuidContainer::$uuidFactory)
                        : RamseyUuid::uuid4();
        };
    }
}
