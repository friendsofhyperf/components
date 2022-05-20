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

use Hyperf\Contract\TranslatorInterface as Accessor;

/**
 * @mixin Accessor
 */
class Translator extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Accessor::class;
    }
}
