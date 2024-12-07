<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Controller;

use FriendsOfHyperf\Telescope\Contract\ClearableRepository;

class EntriesController
{
    /**
     * Delete all of the entries from storage.
     */
    public function destroy(ClearableRepository $storage): void
    {
        $storage->clear();
    }
}
