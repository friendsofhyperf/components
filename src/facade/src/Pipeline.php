<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/3.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade;

use FriendsOfHyperf\Facade\Pipeline\Hub;
use Hyperf\Config\Config as Accessor;

/**
 * @mixin Accessor
 */
class Pipeline extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Hub::class;
    }
}
