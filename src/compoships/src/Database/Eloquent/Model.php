<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/compoships.
 *
 * @link     https://github.com/friendsofhyperf/compoships
 * @document https://github.com/friendsofhyperf/compoships/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Compoships\Database\Eloquent;

use FriendsOfHyperf\Compoships\Compoships;
use Hyperf\Database\Model\Model as Eloquent;

class Model extends Eloquent
{
    use Compoships;
}
