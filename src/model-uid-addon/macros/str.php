<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
use Hyperf\Utils\Str;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Uid\Ulid;

if (! Str::hasMacro('ulid')) {
    Str::macro('ulid', function () {
        return new Ulid();
    });
}

if (! Str::hasMacro('orderedUuid')) {
    Str::macro('orderedUuid', function () {
        return Uuid::uuid7();
    });
}
