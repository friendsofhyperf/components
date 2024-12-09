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

use Carbon\Carbon;
use FriendsOfHyperf\Telescope\EntryType;
use FriendsOfHyperf\Telescope\EntryUpdate;
use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;

use function FriendsOfHyperf\Helpers\response;
use function Hyperf\Collection\collect;

class ExceptionsController extends EntryController
{
    /**
     * Update an entry with the given ID.
     */
    public function update(string $id)
    {
        $entry = $this->storage->find($id);

        if ($this->request->input('resolved_at') === 'now') {
            $update = new EntryUpdate($entry->id, $entry->type, [
                'resolved_at' => Carbon::now()->toDateTimeString(),
            ]);

            $this->storage->update(collect([$update]));

            // Reload entry
            $entry = $this->storage->find($id);
        }

        return response()->json([
            'entry' => $entry,
            'batch' => $this->storage->get(null, EntryQueryOptions::forBatchId($entry->batchId)->limit(-1)),
        ]);
    }

    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::EXCEPTION;
    }
}
