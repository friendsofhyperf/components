<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Compoships\Database\Eloquent;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model as Eloquent;

class Model extends Eloquent
{
    use Compoships;
}
