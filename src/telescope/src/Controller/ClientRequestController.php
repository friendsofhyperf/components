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

use FriendsOfHyperf\Telescope\EntryType;

class ClientRequestController extends EntryController
{
    public function list()
    {
        return $this->index();
    }

    public function detail(string $id)
    {
        return $this->show($id);
    }

    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::CLIENT_REQUEST;
    }

    /**
     * The watcher class for the controller.
     */
    protected function watcher()
    {
        // return RequestWatcher::class;
        return null;
    }
}
