<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\ModelUidAddon;

use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;

class StrMixin
{
    public function ulid()
    {
        return fn () => new Ulid();
    }

    public function orderedUuid()
    {
        return fn () => Uuid::uuid7();
    }
}
