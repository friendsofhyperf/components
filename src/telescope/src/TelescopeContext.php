<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope;

use Hyperf\Context\Context;

use function Hyperf\Coroutine\defer;

class TelescopeContext
{
    public const BATCH_ID = 'telescope.context.batch_id';

    public const SUB_BATCH_ID = 'telescope.context.sub_batch_id';

    public const ENTRIES = 'telescope.context.entries';

    public static function setBatchId(string $batchId): ?string
    {
        return Context::set(self::BATCH_ID, $batchId);
    }

    public static function getBatchId(): ?string
    {
        return Context::get(self::BATCH_ID) ?: null;
    }

    public static function setSubBatchId(string $batchId): ?string
    {
        return Context::set(self::SUB_BATCH_ID, $batchId);
    }

    public static function getSubBatchId(): ?string
    {
        return Context::get(self::SUB_BATCH_ID) ?: null;
    }

    public static function addEntry(IncomingEntry $entry): void
    {
        if (! Context::has(self::ENTRIES)) {
            Context::set(self::ENTRIES, []);

            defer(function () {
                /** @var IncomingEntry[] $entries */
                $entries = Context::get(self::ENTRIES);
                foreach ($entries as $entry) {
                    $entry->create();
                }
                Context::destroy(self::ENTRIES);
            });
        }

        $entries = Context::get(self::ENTRIES);
        $entries[] = $entry;

        Context::set(self::ENTRIES, $entries);
    }
}
