<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Contract;

use DateTimeInterface;

interface PrunableRepository
{
    /**
     * Prune all of the entries older than the given date.
     */
    public function prune(DateTimeInterface $before, bool $keepExceptions): int;
}
