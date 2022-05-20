<?php

declare(strict_types=1);
/**
 * This file is part of hyperf/facade.
 *
 * @link     https://github.com/friendsofhyperf/facade
 * @document https://github.com/friendsofhyperf/facade/blob/1.x/README.md
 * @contact  huangdijia@gmail.com
 */
namespace FriendsOfHyperf\Facade;

use Hyperf\Validation\Contract\ValidatorFactoryInterface as Accessor;

/**
 * @mixin Accessor
 */
class Validator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
