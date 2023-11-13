<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Telescope\Model;

use Hyperf\DbConnection\Model\Model;

class TelescopeEntryTagModel extends Model
{
    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected ?string $table = 'telescope_entries_tags';

    protected array $fillable = [
        'entry_uuid',
        'tag',
    ];
}
