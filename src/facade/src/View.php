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

use Hyperf\View\Render;
use Hyperf\View\RenderInterface;
use Override;

/**
 * @mixin Render
 */
class View extends Facade
{
    #[Override]
    protected static function getFacadeAccessor()
    {
        return RenderInterface::class;
    }
}
