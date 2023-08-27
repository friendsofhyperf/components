<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\ClosureCommand;

use Hyperf\Context\Context;
use Symfony\Component\Console\Input\Input as SymfonyInput;

/**
 * @mixin SymfonyInput
 */
class Input
{
    public function __call($name, $arguments)
    {
        if (Context::has(static::class)) {
            return Context::get(static::class)->{$name}(...$arguments);
        }
    }
}
