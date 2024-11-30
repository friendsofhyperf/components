<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\WebTinker\OutputModifiers;

use function Hyperf\Support\now;

class PrefixDateTime implements OutputModifier
{
    public function modify(string $output = ''): string
    {
        return '<span class="text-dimmed">' . now()->format('Y-m-d H:i:s') . '</span><br>' . $output;
    }
}
