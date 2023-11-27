<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Facade;

use FriendsOfHyperf\Support\Pipeline\Hub;
use Override;

/**
 * @mixin Hub
 */
class Pipeline extends Facade
{
    #[Override]
    protected static function getFacadeAccessor()
    {
        return Hub::class;
    }
}
