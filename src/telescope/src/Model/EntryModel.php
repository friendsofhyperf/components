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

use FriendsOfHyperf\Telescope\Storage\EntryQueryOptions;
use Hyperf\Database\Model\Collection;

/**
 * @deprecated since v3.1, use `\FriendsOfHyperf\Telescope\Storage\Model\EntryModel` instead, will be removed in v3.2
 *
 * @property string $id
 * @property int $sequence
 * @property string $uuid
 * @property string $batch_id
 * @property string $sub_batch_id
 * @property string $family_hash
 * @property bool $should_display_on_index
 * @property string $type
 * @property array $content
 * @property Collection<int,\FriendsOfHyperf\Telescope\Storage\Model\EntryTagModel> $tags
 * @property \Carbon\Carbon $created_at
 * @method static \Hyperf\Database\Model\Builder withTelescopeOptions(string $type, EntryQueryOptions $options = null)
 */
class EntryModel extends \FriendsOfHyperf\Telescope\Storage\Model\EntryModel
{
}
