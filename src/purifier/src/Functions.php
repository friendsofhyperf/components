<?php

declare(strict_types=1);
/**
 * This file is part of friendsofhyperf/components.
 *
 * @link     https://github.com/friendsofhyperf/components
 * @document https://github.com/friendsofhyperf/components/blob/main/README.md
 * @contact  huangdijia@gmail.com
 */

namespace FriendsOfHyperf\Purifier;

use Closure;
use Hyperf\Context\ApplicationContext;

if (! function_exists('clean')) {
    /**
     * @template T
     * @param T $dirty
     * @return ($dirty is string ? string : ($dirty is array ? array : T))
     */
    function clean(mixed $dirty, array|string|null $config = null, ?Closure $postCreateConfigHook = null)
    {
        return ApplicationContext::getContainer()->get(Purifier::class)->clean($dirty, $config, $postCreateConfigHook);
    }
}
